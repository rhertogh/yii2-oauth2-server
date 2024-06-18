<?php

namespace rhertogh\Yii2Oauth2Server\components\authorization\EndSession\base;

use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use League\OAuth2\Server\RedirectUriValidators\RedirectUriValidator;
use rhertogh\Yii2Oauth2Server\components\authorization\base\Oauth2BaseAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\helpers\UrlHelper;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\EndSession\Oauth2EndSessionAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use yii\base\InvalidCallException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;

abstract class Oauth2BaseEndSessionAuthorizationRequest extends Oauth2BaseAuthorizationRequest implements Oauth2EndSessionAuthorizationRequestInterface
{
    /**
     * @var string|null
     */
    protected $_idTokenHint;

    /**
     * @var string|null
     */
    protected $_endSessionUrl;

    /**
     * @var bool
     */
    protected $_validatedRequest = false;

    /**
     * @var bool|null
     */
    protected $_endUserAuthorizationRequired = null;

    public function __serialize()
    {
        return array_merge(parent::__serialize(), [
            '_idTokenHint' => $this->_idTokenHint,
            '_endSessionUrl' => $this->_endSessionUrl,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getIdTokenHint()
    {
        return $this->_idTokenHint;
    }

    /**
     * @inheritDoc
     */
    public function setIdTokenHint($idTokenHint)
    {
        $this->_idTokenHint = $idTokenHint;
        $this->_validatedRequest = false;
        return $this;
    }

    public function setClientIdentifier($clientIdentifier)
    {
        $this->_validatedRequest = false;
        return parent::setClientIdentifier($clientIdentifier);
    }

    /**
     * @inheritDoc
     */
    public function getEndSessionUrl()
    {
        return $this->_endSessionUrl;
    }

    /**
     * @inheritDoc
     */
    public function setEndSessionUrl($endSessionUrl)
    {
        $this->_endSessionUrl = $endSessionUrl;
        return $this;
    }

    public function validateRequest()
    {
        $module = $this->getModule();
        $idTokenHint = $this->getIdTokenHint();
        $clientIdentifier = $this->getClientIdentifier();
        $identity = $module->getUserIdentity();
        $postLogoutRedirectUri = $this->getRedirectUri();

        $endUserConfirmationMayBeSkipped = false;

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
                    $this->setClientIdentifier($clientIdentifier);
                } else {
                    throw new BadRequestHttpException(
                        'The `client_id` parameter is required when there are multiple audiences'
                        . ' in the "aud" claim of the `id_token_hint`.'
                    );
                }
            }

            if ($identity) {
                if ($validator->validate($idToken, new RelatedTo((string)$identity->getIdentifier()))) {
                    $endUserConfirmationMayBeSkipped = true;
                }
            }
        } else {
            if (!$module->openIdConnectAllowAnonymousRpInitiatedLogout) {
                throw new BadRequestHttpException('The `id_token_hint` parameter is required.');
            }
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

        if ($postLogoutRedirectUri) {
            if (!empty($client)) {
                $allowedPostLogoutRedirectUris = $client->getPostLogoutRedirectUris();
                $validatePostLogoutRedirectUri = $postLogoutRedirectUri;
                if ($client->isVariableRedirectUriQueryAllowed()) {
                    $validatePostLogoutRedirectUri = UrlHelper::stripQueryAndFragment($validatePostLogoutRedirectUri);
                }

                $validator = new RedirectUriValidator($allowedPostLogoutRedirectUris);
                if (!$validator->validateRedirectUri($validatePostLogoutRedirectUri)) {
                    throw new UnauthorizedHttpException('Invalid `post_logout_redirect_uri`.');
                }
            } else {
                throw new UnauthorizedHttpException('`post_logout_redirect_uri` is only allowed if the client is known.');
            }
        }

        $endUserAuthorizationRequired = !(
            $endUserConfirmationMayBeSkipped
            && isset($client)
            && (
                $client->getOpenIdConnectRpInitiatedLogout()
                    === Oauth2ClientInterface::OIDC_RP_INITIATED_LOGOUT_ENABLED_WITHOUT_CONFIRMATION
            )
        );

        $this->setEndUserAuthorizationRequired($endUserAuthorizationRequired);
        $this->_validatedRequest = true;
    }

    public function getEndUserAuthorizationRequired()
    {
        if (!$this->_validatedRequest) {
            throw new InvalidCallException('Request must be validated first');
        }

        return $this->_endUserAuthorizationRequired;
    }

    public function setEndUserAuthorizationRequired($endUserAuthorizationRequired)
    {
        $this->_endUserAuthorizationRequired = $endUserAuthorizationRequired;
        return $this;
    }

}
