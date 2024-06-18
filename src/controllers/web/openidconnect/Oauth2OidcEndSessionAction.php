<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web\openidconnect;

use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use League\OAuth2\Server\RedirectUriValidators\RedirectUriValidator;
use rhertogh\Yii2Oauth2Server\controllers\web\base\Oauth2BaseWebAction;
use rhertogh\Yii2Oauth2Server\controllers\web\Oauth2OidcController;
use rhertogh\Yii2Oauth2Server\helpers\UrlHelper;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\web\openidconnect\Oauth2OidcEndSessionActionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2OidcUserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

/**
 * @property Oauth2OidcController $controller
 */
class Oauth2OidcEndSessionAction extends Oauth2BaseWebAction implements Oauth2OidcEndSessionActionInterface
{
    /**
     * @see https://openid.net/specs/openid-connect-rpinitiated-1_0.html
     * @return Response
     * @throws InvalidConfigException
     */
    public function run()
    {
        $module = $this->controller->module;
        $request = Yii::$app->request;
        $identity = $module->getUserIdentity();

        $logoutVerificationRequired = false;

        if (!$module->enableOpenIdConnect) {
            throw new ForbiddenHttpException('OpenID Connect is disabled.');
        }

        if ($identity && !($identity instanceof Oauth2OidcUserInterface)) {
            throw new InvalidConfigException('In order to support OpenID Connect '
                . get_class($identity) . ' must implement ' . Oauth2OidcUserInterface::class);
        }

        $clientIdentifier = $this->getRequestParam($request, 'client_id');
        $idTokenHint = $this->getRequestParam($request, 'id_token_hint');
        $state = $this->getRequestParam($request, 'state');
        $postLogoutRedirectUri = $this->getRequestParam($request, 'post_logout_redirect_uri');

        // The `id_token_hint` is the OIDC id token (https://openid.net/specs/openid-connect-core-1_0.html#IDToken)
        if ($idTokenHint) {

            $parser = new Parser(new JoseEncoder());

            $idToken = $parser->parse($idTokenHint);

            $validator = new Validator();

            if (!$validator->validate($idToken, new SignedWith(
                new Sha256(),
                InMemory::plainText($module->getPublicKey()->getKeyContents())
            ))) {
                throw new UnauthorizedHttpException('Invalid `id_token_hint` signature.');
            }

            if ($clientIdentifier) {
                if (!$validator->validate($idToken, new PermittedFor($clientIdentifier))) {
                    throw new UnauthorizedHttpException('Invalid "aud" claim in `id_token_hint`.');
                }
            } else {
                $audiences = $idToken->claims()->get('aud');
                if (count($audiences) === 1) {
                    $clientIdentifier = $audiences[0];
                } else {
                    throw new BadRequestHttpException(
                        'The `client_id` parameter is required when there are multiple audiences'
                        . ' in the "aud" claim of the `id_token_hint`.'
                    );
                }
            }

            if ($identity) {
                if (!$validator->validate($idToken, new RelatedTo((string)$identity->getIdentifier()))) {
                    $logoutVerificationRequired = true;
                }
            }
        } else {
            if (!$module->openIdConnectAllowAnonymousRpInitiatedLogout) {
                throw new BadRequestHttpException('The `id_token_hint` parameter is required.');
            }
            $logoutVerificationRequired = true;
        }

        if ($clientIdentifier) {
            $client = $module->getClientRepository()->getClientEntity($clientIdentifier);
            if (!$client || !$client->isEnabled()) {
                throw new ForbiddenHttpException('Client "' . $clientIdentifier . '" not found or disabled.');
            }

            if (
                !($client->getOpenIdConnectRpInitiatedLogout()
                    > Oauth2ClientInterface::OIDC_RP_INITIATED_LOGOUT_DISABLED)
            ) {
                throw new ForbiddenHttpException('Client "' . $clientIdentifier . '" is not allowed to initiated end-user logout.');
            }
        }

        if (!$logoutVerificationRequired) {
            if (isset($client)) {
                if (
                    $client->getOpenIdConnectRpInitiatedLogout()
                        !== Oauth2ClientInterface::OIDC_RP_INITIATED_LOGOUT_ENABLED_WITHOUT_VERIFICATION
                ) {
                    $logoutVerificationRequired = true;
                }
            } else {
                $logoutVerificationRequired = true;
            }
        }

        if ($logoutVerificationRequired) {
            throw new \LogicException('Not yet implemented: oidc_rp_initiated_logout is currently only supported without end-user verification.');
        }

        $module->logoutUser();

        if (empty($client) || empty($postLogoutRedirectUri)) {
            return $this->controller->goHome();
        }

        $allowedPostLogoutRedirectUris = $client->getPostLogoutRedirectUris();
        $validatePostLogoutRedirectUri = $postLogoutRedirectUri;
        if ($client->isVariableRedirectUriQueryAllowed()) {
            $validatePostLogoutRedirectUri = UrlHelper::stripQueryAndFragment($validatePostLogoutRedirectUri);
        }

        $validator = new RedirectUriValidator($allowedPostLogoutRedirectUris);
        if (!$validator->validateRedirectUri($validatePostLogoutRedirectUri)) {
            throw new UnauthorizedHttpException('Invalid `post_logout_redirect_uri`.');
        }

        $redirectUri = UrlHelper::addQueryParams($postLogoutRedirectUri, ['state' => $state]);

        return $this->controller->redirect($redirectUri);
    }
}
