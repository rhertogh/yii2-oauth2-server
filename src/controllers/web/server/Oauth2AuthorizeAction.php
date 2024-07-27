<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web\server;

use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2ServerController;
use rhertogh\Yii2Oauth2Server\controllers\web\server\base\Oauth2BaseServerAction;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2OidcServerException;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2ServerException;
use rhertogh\Yii2Oauth2Server\helpers\Psr7Helper;
use rhertogh\Yii2Oauth2Server\helpers\UrlHelper;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\client\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\request\Oauth2OidcAuthenticationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\user\Oauth2OidcUserComponentInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\server\Oauth2AuthorizeActionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2UserAuthenticatedAtInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\UnauthorizedHttpException;

/**
 * @property Oauth2ServerController $controller
 */
class Oauth2AuthorizeAction extends Oauth2BaseServerAction implements Oauth2AuthorizeActionInterface
{
    public function run($clientAuthorizationRequestId = null)
    {
        try {
            $request = Yii::$app->request;
            $user = Yii::$app->user;
            $module = $this->controller->module;

            if ($module->enableOpenIdConnect) {
                if (!($user instanceof Oauth2OidcUserComponentInterface)) {
                    throw new InvalidConfigException(
                        'OpenId Connect is enabled but user component does not implement '
                        . Oauth2OidcUserComponentInterface::class
                    );
                }

                $oidcRequest = $this->getRequestParam(
                    $request,
                    Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_REQUEST
                );
                if ($oidcRequest !== null) {
                    throw Oauth2OidcServerException::requestParameterNotSupported();
                }

                $oidcRequestUri = $this->getRequestParam(
                    $request,
                    Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_REQUEST_URI
                );
                if ($oidcRequestUri !== null) {
                    throw Oauth2OidcServerException::requestUriParameterNotSupported();
                }
            }

            $server = $module->getAuthorizationServer();
            $psr7Request = Psr7Helper::yiiToPsr7Request($request);
            $authRequest = $server->validateAuthorizationRequest($psr7Request);

            $requestedScopeIdentifiers = array_map(fn($scope) => $scope->getIdentifier(), $authRequest->getScopes());

            /** @var Oauth2ClientInterface $client */
            $client = $authRequest->getClient();

            $module->validateAuthRequestScopes($client, $requestedScopeIdentifiers, $authRequest->getRedirectUri());

            if (
                !$client->isAuthCodeWithoutPkceAllowed()
                // PKCE is not supported in the implicit flow.
                && $authRequest->getGrantTypeId() != Oauth2Module::GRANT_TYPE_IDENTIFIER_IMPLICIT
            ) {
                if (empty($request->get('code_challenge'))) {
                    throw new BadRequestHttpException(
                        'PKCE is required for this client when using grant type "'
                            . $authRequest->getGrantTypeId() . '".'
                    );
                } elseif ($request->get('code_challenge_method', 'plain') === 'plain') {
                    throw new BadRequestHttpException('PKCE code challenge mode "plain" is not allowed.');
                }
            }

            if ($clientAuthorizationRequestId) {
                $clientAuthorizationRequest = $module->getClientAuthReqSession($clientAuthorizationRequestId);
                if (
                    $clientAuthorizationRequest
                    && $clientAuthorizationRequest->getState()
                    && !Yii::$app->security->compareString(
                        $clientAuthorizationRequest->getState(),
                        $authRequest->getState()
                    )
                ) {
                    throw new UnauthorizedHttpException('Invalid state.');
                }
            }

            if (empty($clientAuthorizationRequest)) {
                $prompts = explode(
                    ' ',
                    (string)$this->getRequestParam(
                        $request,
                        Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_PROMPT
                    )
                )
                    ?? [];

                if (
                    in_array(Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_PROMPT_NONE, $prompts)
                    && (count($prompts) > 1)
                ) {
                    throw new BadRequestHttpException(
                        'When the "prompt" parameter contains "none" other values are not allowed.'
                    );
                }

                // Ignore `offline_access` scope if prompt doesn't contain 'consent' (or pre-approved via config).
                // See https://openid.net/specs/openid-connect-core-1_0.html#OfflineAccess.
                if (
                    in_array(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OFFLINE_ACCESS, $requestedScopeIdentifiers)
                    && !in_array(Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_PROMPT_CONSENT, $prompts)
                    && !$client->getOpenIdConnectAllowOfflineAccessWithoutConsent()
                ) {
                    $requestedScopeIdentifiers = array_diff(
                        $requestedScopeIdentifiers,
                        [Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_OFFLINE_ACCESS]
                    );
                }

                $maxAge = $this->getRequestParam(
                    $request,
                    Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_MAX_AGE
                );
                if ($maxAge === '') {
                    $maxAge = null;
                } elseif ($maxAge !== null) {
                    $maxAge = (int)$maxAge;
                }

                /** @var Oauth2ClientAuthorizationRequestInterface $clientAuthorizationRequest */
                $clientAuthorizationRequest = Yii::createObject([
                    'class' => Oauth2ClientAuthorizationRequestInterface::class,
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

            if (
                in_array(
                    Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_PROMPT_CREATE,
                    $module->getSupportedPromptValues()
                )
                && in_array(
                    Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_PROMPT_CREATE,
                    $clientAuthorizationRequest->getPrompts()
                )
                && !$clientAuthorizationRequest->getCreateUserPromptProcessed()
            ) {
                if (!$request->get('createUserPromtProcessed')) {
                    $module->setClientAuthReqSession($clientAuthorizationRequest);
                    $returnUrl = $clientAuthorizationRequest->getAuthorizationRequestUrl();
                    $returnUrl = UrlHelper::addQueryParams($returnUrl, ['createUserPromtProcessed' => 'true']);
                    $user->setReturnUrl($returnUrl);

                    return Yii::$app->response->redirect($module->userAccountCreationUrl);
                } else {
                    if ($user->isGuest) {
                        throw new BadRequestHttpException('The `createUserPromtProcessed` parameter is set, but no user is logged in.');
                    }
                    $clientAuthorizationRequest->setCreateUserPromptProcessed(true);
                }
            }

            if ($user->isGuest) {
                if (
                    in_array(
                        Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_PROMPT_NONE,
                        $clientAuthorizationRequest->getPrompts()
                    )
                ) {
                    // User authentication disallowed by OpenID Connect.
                    throw Oauth2OidcServerException::loginRequired($authRequest->getRedirectUri());
                }

                $module->setClientAuthReqSession($clientAuthorizationRequest);
                $user->setReturnUrl($clientAuthorizationRequest->getAuthorizationRequestUrl());
//                $user->setReturnUrl(UrlHelper::addQueryParams($request->absoluteUrl, [
//                    'clientAuthorizationRequestId' => $clientAuthorizationRequest->getRequestId(),
//                ]));

                return Yii::$app->response->redirect($user->loginUrl);
            }

            // Check if reauthentication is required.
            $reauthenticationRequired = false;
            if (!$clientAuthorizationRequest->wasUserAthenticatedDuringRequest()) {
                if (
                    (// true in case user was authenticated before request and oidc prompt requires login.
                        in_array(
                            Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_PROMPT_LOGIN,
                            $clientAuthorizationRequest->getPrompts()
                        )
                        && $clientAuthorizationRequest->wasUserAuthenticatedBeforeRequest()
                    )
                ) {
                    $reauthenticationRequired = true;
                }

                if (
                    !$reauthenticationRequired // Prevent unnecessary checking.
                    && $clientAuthorizationRequest->getMaxAge() !== null
                ) {
                    $appUserIdentity = $module->getUserIdentity();
                    if (!($appUserIdentity instanceof Oauth2UserAuthenticatedAtInterface)) {
                        throw new InvalidConfigException(
                            'The authorization request max age is set, but ' . get_class($appUserIdentity)
                            . ' does not implement ' . Oauth2UserAuthenticatedAtInterface::class
                        );
                    }
                    $latestAuthenticatedAt = $appUserIdentity->getLatestAuthenticatedAt();
                    if (
                        ($latestAuthenticatedAt === null)
                        || ( // if $latestAuthenticatedAt is not null, check if it's before the max time allowed.
                            (time() - $latestAuthenticatedAt->getTimestamp()) > $clientAuthorizationRequest->getMaxAge()
                        )
                    ) {
                        $reauthenticationRequired = true;
                    }
                }
            }

            if ($reauthenticationRequired) {
                // Prevent redirect loop.
                $redirectAttempt = (int)$request->get('redirectAttempt', 0);
                if ($redirectAttempt > 3) {
                    // This error most likely occurs if the User Controller does not correctly performs
                    // user reauthentication and redirect back to this action without calling
                    // `setUserAuthenticatedDuringRequest(true)` on the $clientAuthorizationRequest.
                    throw new HttpException(
                        501,
                        'Reauthentication not correctly implemented, aborting due to redirect loop.'
                    );
                }

                $module->setClientAuthReqSession($clientAuthorizationRequest);
                $user->setReturnUrl(
                    UrlHelper::addQueryParams($clientAuthorizationRequest->getAuthorizationRequestUrl(), [
                        'redirectAttempt' => $redirectAttempt + 1,
                    ])
                );
                return $user->reauthenticationRequired($clientAuthorizationRequest);
            }

            $userAccountSelection = $client->getUserAccountSelection() ?? $module->defaultUserAccountSelection;

            if (empty($clientAuthorizationRequest->getUserIdentity())) {
                if (
                    ($userAccountSelection === Oauth2Module::USER_ACCOUNT_SELECTION_ALWAYS)
                    || (
                        $userAccountSelection === Oauth2Module::USER_ACCOUNT_SELECTION_UPON_CLIENT_REQUEST
                        && in_array(
                            Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_PROMPT_SELECT_ACCOUNT,
                            $clientAuthorizationRequest->getPrompts()
                        )
                    )
                ) {
                    if (
                        in_array(
                            Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_PROMPT_NONE,
                            $clientAuthorizationRequest->getPrompts()
                        )
                    ) {
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

            if (
                $clientAuthorizationRequest->getUserIdentity()->isOauth2ClientAllowed(
                    $client,
                    $clientAuthorizationRequest->getGrantType()
                ) !== true
            ) {
                throw Oauth2ServerException::accessDenied(
                    Yii::t('oauth2', 'User {user_id} is not allowed to use client {client_identifier}.', [
                        'user_id' => $user->getId(),
                        'client_identifier' => $client->getIdentifier(),
                    ])
                );
            }

            if (
                $clientAuthorizationRequest->isAuthorizationNeeded()
                || in_array(
                    Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_PROMPT_CONSENT,
                    $clientAuthorizationRequest->getPrompts()
                )
            ) {
                if (!$clientAuthorizationRequest->isAuthorizationAllowed()) {
                    throw Oauth2ServerException::authorizationNotAllowed();
                }

                if (
                    in_array(
                        Oauth2OidcAuthenticationRequestInterface::REQUEST_PARAMETER_PROMPT_NONE,
                        $clientAuthorizationRequest->getPrompts()
                    )
                ) {
                    // User consent is disallowed by OpenID Connect.
                    throw Oauth2OidcServerException::consentRequired($authRequest->getRedirectUri());
                }

                if ($clientAuthorizationRequest->isCompleted()) {
                    $authorizationApproved = $clientAuthorizationRequest->isApproved();
                    // Cleanup session data.
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

            $psr7Response = Psr7Helper::yiiToPsr7Response(Yii::$app->response);
            $psr7Response = $server->completeAuthorizationRequest($authRequest, $psr7Response);

            return Psr7Helper::psr7ToYiiResponse($psr7Response);
        } catch (\Exception $e) {
            return $this->processException($e, __METHOD__);
        }
    }
}
