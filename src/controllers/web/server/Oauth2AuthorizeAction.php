<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web\server;

use GuzzleHttp\Psr7\Response as Psr7Response;
use League\OAuth2\Server\Exception\OAuthServerException;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ServerController;
use rhertogh\Yii2Oauth2Server\controllers\web\server\base\Oauth2BaseServerAction;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2OidcServerException;
use rhertogh\Yii2Oauth2Server\helpers\Psr7Helper;
use rhertogh\Yii2Oauth2Server\helpers\UrlHelper;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ClientAuthorizationRequestInterface as ClientAuthRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\request\Oauth2OidcAuthenticationRequestInterface as OidcAuthRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface as OidcScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\user\Oauth2OidcUserComponentInterface as OidcUserComponentInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface as ClientInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\Request;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * @property Oauth2ServerController $controller
 */
class Oauth2AuthorizeAction extends Oauth2BaseServerAction
{
    public function run($clientAuthorizationRequestId = null)
    {
        try {
            $request = Yii::$app->request;
            /** @var yii\web\User $user */
            $user = Yii::$app->user;
            $module = $this->controller->module;

            if ($module->enableOpenIdConnect) {
                if (!($user instanceof OidcUserComponentInterface)) {
                    throw new InvalidConfigException('OpenId Connect is enabled but user component does not implement ' . OidcUserComponentInterface::class);
                }

                $oidcRequest = $this->getRequestParam($request, OidcAuthRequestInterface::REQUEST_PARAMETER_REQUEST);
                if ($oidcRequest !== null) {
                    throw Oauth2OidcServerException::requestParameterNotSupported();
                }

                $oidcRequestUri = $this->getRequestParam($request, OidcAuthRequestInterface::REQUEST_PARAMETER_REQUEST_URI);
                if ($oidcRequestUri !== null) {
                    throw Oauth2OidcServerException::requestUriParameterNotSupported();
                }
            }

            $server = $module->getAuthorizationServer();
            $psr7Request = Psr7Helper::yiiToPsr7Request($request);
            $authRequest = $server->validateAuthorizationRequest($psr7Request);

            $requestedScopeIdentifiers = array_map(fn($scope) => $scope->getIdentifier(), $authRequest->getScopes());

            /** @var ClientInterface $client */
            $client = $authRequest->getClient();

            if (!$client->validateAuthRequestScopes($requestedScopeIdentifiers, $unauthorizedScopes)) {
                throw OAuthServerException::invalidScope(array_shift($unauthorizedScopes), $authRequest->getRedirectUri());
            }

            //Orig $openIdConnectActive, if ($openIdConnectActive) {



            if (
                !$client->isAuthCodeWithoutPkceAllowed()
                && $authRequest->getGrantTypeId() != Oauth2Module::GRANT_TYPE_IDENTIFIER_IMPLICIT // PKCE is not supported in the implicit flow
            ) {
                if (empty($request->get('code_challenge'))) {
                    throw new BadRequestHttpException('PKCE is required for this client when using grant type "' . $authRequest->getGrantTypeId() . '".');
                } elseif ($request->get('code_challenge_method', 'plain') === 'plain') {
                    throw new BadRequestHttpException('PKCE code challenge mode "plain" is not allowed.');
                }
            }

            if ($clientAuthorizationRequestId) {
                $clientAuthorizationRequest = $module->getClientAuthReqSession($clientAuthorizationRequestId);
                if ($clientAuthorizationRequest
                    && $clientAuthorizationRequest->getState()
                    && !Yii::$app->security->compareString($clientAuthorizationRequest->getState(), $authRequest->getState())
                ) {
                    throw new UnauthorizedHttpException('Invalid state.');
                }
            }

            if (empty($clientAuthorizationRequest)) {

                $prompts = explode(' ', $this->getRequestParam($request, OidcAuthRequestInterface::REQUEST_PARAMETER_PROMPT)) ?? [];

                if (in_array(OidcAuthRequestInterface::REQUEST_PARAMETER_PROMPT_NONE, $prompts)
                    && (count($prompts) > 1)
                ) {
                    throw new BadRequestHttpException('When the "prompt" parameter contains "none" other values are not allowed.');
                }

                // Ignore `offline_access` scope if prompt doesn't contain 'consent' (or pre-approved via config)
                // https://openid.net/specs/openid-connect-core-1_0.html#OfflineAccess
                if (in_array(OidcScopeInterface::OPENID_CONNECT_SCOPE_OFFLINE_ACCESS, $requestedScopeIdentifiers)
                    && !in_array(OidcAuthRequestInterface::REQUEST_PARAMETER_PROMPT_CONSENT, $prompts)
                    && !$client->getOpenIdConnectAllowOfflineAccessWithoutConsent()
                ) {
                    $requestedScopeIdentifiers = array_diff(
                        $requestedScopeIdentifiers,
                        [OidcScopeInterface::OPENID_CONNECT_SCOPE_OFFLINE_ACCESS]
                    );
                }

                $maxAge = $this->getRequestParam($request, OidcAuthRequestInterface::REQUEST_PARAMETER_MAX_AGE);
                if ($maxAge === '') {
                    $maxAge = null;
                } elseif($maxAge !== null) {
                    $maxAge = (int)$maxAge;
                }

                /** @var ClientAuthRequestInterface $clientAuthorizationRequest */
                $clientAuthorizationRequest = Yii::createObject([
                    'class' => ClientAuthRequestInterface::class,
                    'module' => $module,
                    'userAuthenticatedBeforeRequest' => !$user->isGuest,
                    'clientIdentifier' => $authRequest->getClient()->getIdentifier(),
                    'state' => $authRequest->getState(),
                    'requestedScopeIdentifiers' => $requestedScopeIdentifiers,
                    'grantType' => $authRequest->getGrantTypeId(),
                    'authorizeUrl' => $request->absoluteUrl,
                    'redirectUri' => $authRequest->getRedirectUri(),
                    'prompts' => $prompts,
                    'maxAge' => $maxAge,
                ]);
            }

            if ($user->isGuest) {
                if (in_array(OidcAuthRequestInterface::REQUEST_PARAMETER_PROMPT_NONE, $clientAuthorizationRequest->getPrompts())) {
                    // User authentication disallowed by OpenID Connect
                    throw Oauth2OidcServerException::loginRequired($authRequest->getRedirectUri());
                }

                $module->setClientAuthReqSession($clientAuthorizationRequest);
                $user->setReturnUrl($clientAuthorizationRequest->getAuthorizationRequestUrl());
//                $user->setReturnUrl(UrlHelper::addQueryParams($request->absoluteUrl, [
//                    'clientAuthorizationRequestId' => $clientAuthorizationRequest->getRequestId(),
//                ]));

                return Yii::$app->response->redirect($user->loginUrl);
            }

            // Check if reauthentication is required
            if ((
                    ( // true in case user was authenticated before request and oidc prompt requires login
                        in_array(OidcAuthRequestInterface::REQUEST_PARAMETER_PROMPT_LOGIN, $clientAuthorizationRequest->getPrompts())
                        && $clientAuthorizationRequest->wasUserAuthenticatedBeforeRequest()
                    )
                    ||
                    ( // true in case oidc max_age is set and the user was authenticated before the maximum time allowed
                        $clientAuthorizationRequest->getMaxAge() !== null
                        && (time() - $module->getUserIdentity()->getLatestAuthenticatedAt()->getTimestamp()) > $clientAuthorizationRequest->getMaxAge()
                    )
                )
                && !$clientAuthorizationRequest->wasUserAthenticatedDuringRequest()
            ) {
                // Prevent redirect loop
                $redirectAttempt = (int)$request->get('redirectAttempt', 0);
                if ($redirectAttempt > 3) {
                    // This error most likely occurs if the User Controller does not correctly perform user reauthentication
                    // and redirect back to this action without calling `setUserAuthenticatedDuringRequest(true)`
                    // on the $clientAuthorizationRequest
                    throw new HttpException(501, 'Reauthentication not correctly implemented, aborting due to redirect loop.');
                }

                $module->setClientAuthReqSession($clientAuthorizationRequest);
                $user->setReturnUrl(UrlHelper::addQueryParams($clientAuthorizationRequest->getAuthorizationRequestUrl(), [
                    'redirectAttempt' => $redirectAttempt + 1,
                ]));
                return $user->reauthenticationRequired($clientAuthorizationRequest);
            }

            $userAccountSelection = $client->getUserAccountSelection() ?? $module->defaultUserAccountSelection;

            if (empty($clientAuthorizationRequest->getUserIdentity())) {
                if (
                    ($userAccountSelection === Oauth2Module::USER_ACCOUNT_SELECTION_ALWAYS)
                    || (
                        $userAccountSelection === Oauth2Module::USER_ACCOUNT_SELECTION_UPON_CLIENT_REQUEST
                        && in_array(OidcAuthRequestInterface::REQUEST_PARAMETER_PROMPT_SELECT_ACCOUNT, $clientAuthorizationRequest->getPrompts())
                    )
                ) {
                    if (in_array(OidcAuthRequestInterface::REQUEST_PARAMETER_PROMPT_NONE, $clientAuthorizationRequest->getPrompts())) {
                        throw Oauth2OidcServerException::accountSelectionRequired(
                            'User account selection is required but the "prompt" parameter is set to "none".',
                            $authRequest->getRedirectUri()
                        );
                    }
                    $accountSelectionRequiredResponse = $user->accountSelectionRequired($clientAuthorizationRequest);
                    if ($accountSelectionRequiredResponse === false) {
                        throw Oauth2OidcServerException::accountSelectionRequired(
                            'User account selection is not supported by the server.',
                            $authRequest->getRedirectUri(),
                        );
                    }
                    $module->setClientAuthReqSession($clientAuthorizationRequest);
                    $user->setReturnUrl($clientAuthorizationRequest->getAuthorizationRequestUrl());
                    return $accountSelectionRequiredResponse;
                } else {
                    $clientAuthorizationRequest->setUserIdentity($module->getUserIdentity());
                }
            }

            if ($clientAuthorizationRequest->isAuthorizationNeeded()
                || in_array(OidcAuthRequestInterface::REQUEST_PARAMETER_PROMPT_CONSENT, $clientAuthorizationRequest->getPrompts())
            ) {
                if (in_array(OidcAuthRequestInterface::REQUEST_PARAMETER_PROMPT_NONE, $clientAuthorizationRequest->getPrompts())) {
                    // User consent is disallowed by OpenID Connect
                    throw Oauth2OidcServerException::consentRequired($authRequest->getRedirectUri());
                }
                if ($clientAuthorizationRequest->isCompleted()) {
                    $authorizationApproved = $clientAuthorizationRequest->isApproved();
                    // Cleanup session data
                    $module->removeClientAuthReqSession($clientAuthorizationRequest->getRequestId());
                } else {
                    return $module->generateClientAuthReqRedirectResponse($clientAuthorizationRequest);
                }
            } else {
                // All scopes are already approved (or are default).
                $authorizationApproved = true;
            }

            $authRequest->setUser($clientAuthorizationRequest->getUserIdentity());
            $authRequest->setAuthorizationApproved($authorizationApproved);

            $psr7Response = Yii::createObject(Psr7Response::class);
            $psr7Response = $server->completeAuthorizationRequest($authRequest, $psr7Response);

            return Psr7Helper::psr7ToYiiResponse($psr7Response);

//        } catch (OAuthServerException $e) {
//            return $this->processOAuthServerException($e);
        } catch (\Exception $e) {
            Yii::error((string)$e, __METHOD__);
            return $this->processException($e);
//            $message = Yii::t('oauth2', 'Unable to respond to authorization request.');
//            throw Oauth2ServerHttpException::createFromException($message, $e);
        }
    }

    /**
     * @param Request $request
     * @param string $name
     * @return mixed
     */
    protected function getRequestParam($request, $name, $defaultValue = null)
    {
        return $request->post($name) ?? $request->get($name, $defaultValue);
    }
}
