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
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\client\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\EndSession\Oauth2EndSessionAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2OidcUserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
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
    public function run($endSessionAuthorizationRequestId = null)
    {
        $module = $this->controller->module;
        $request = Yii::$app->request;
        $identity = $module->getUserIdentity();

        if (!$module->enableOpenIdConnect) {
            throw new ForbiddenHttpException('OpenID Connect is disabled.');
        }

        if ($identity && !($identity instanceof Oauth2OidcUserInterface)) {
            throw new InvalidConfigException('In order to support OpenID Connect '
                . get_class($identity) . ' must implement ' . Oauth2OidcUserInterface::class);
        }

        if (empty($endSessionAuthorizationRequestId)) {
            /** @var Oauth2EndSessionAuthorizationRequestInterface $endSessionAuthorizationRequest */
            $endSessionAuthorizationRequest = Yii::createObject([
                'class' => Oauth2EndSessionAuthorizationRequestInterface::class,
                'module' => $module,
                'idTokenHint' => $this->getRequestParam($request, 'id_token_hint'),
                'clientIdentifier' => $this->getRequestParam($request, 'client_id'),
                'endSessionUrl' => $request->absoluteUrl,
                'redirectUri' => $this->getRequestParam($request, 'post_logout_redirect_uri'),
                'state' => $this->getRequestParam($request, 'state'),
            ]);

            $endSessionAuthorizationRequest->validateRequest();

            if (!$identity) {
                /**
                 * Specified in https://openid.net/specs/openid-connect-rpinitiated-1_0.html#ValidationAndErrorHandling
                 * "Note that because RP-Initiated Logout Requests are intended to be idempotent,
                 *  it is explicitly not an error for an RP to request that a logout be performed when the OP does not
                 *  consider that the End-User is logged in with the OP at the requesting RP."
                 */
                return $this->controller->redirect($endSessionAuthorizationRequest->getRequestCompletedRedirectUrl(true));
            }

            if ($endSessionAuthorizationRequest->getEndUserAuthorizationRequired()) {
                if ($endSessionAuthorizationRequest->isAuthorizationAllowed()) {
                    return $module->generateEndSessionAuthReqRedirectResponse($endSessionAuthorizationRequest);
                } else {
                    throw new UnauthorizedHttpException(Yii::t('oauth2', 'You are not allowed to authorize logging out.'));
                }
            } else {
                $endSessionAuthorizationRequest->autoApproveAndProcess();
            }
        } else {
            $endSessionAuthorizationRequest = $module->getEndSessionAuthReqSession($endSessionAuthorizationRequestId);
            if (empty($endSessionAuthorizationRequest)) {
                throw new NotFoundHttpException('End Session authorization request not found.');
            }
        }

        if (!$endSessionAuthorizationRequest->isCompleted()) {
            throw new BadRequestHttpException('End Session authorization is not completed.');
        }

        return $this->controller->redirect($endSessionAuthorizationRequest->getRequestCompletedRedirectUrl());
    }
}
