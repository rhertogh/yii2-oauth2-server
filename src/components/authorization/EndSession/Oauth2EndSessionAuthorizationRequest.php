<?php

namespace rhertogh\Yii2Oauth2Server\components\authorization\EndSession;

use rhertogh\Yii2Oauth2Server\components\authorization\base\Oauth2BaseAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\components\authorization\EndSession\base\Oauth2BaseEndSessionAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\helpers\UrlHelper;
use Yii;
use yii\base\InvalidCallException;

class Oauth2EndSessionAuthorizationRequest extends Oauth2BaseEndSessionAuthorizationRequest
{
    public function isAuthorizationAllowed()
    {
        return $this->getModule()->getUserIdentity() !== null;
    }

    public function processAuthorization()
    {
        if ($this->isApproved()) {
            $this->getModule()->logoutUser();
        }

        $this->setCompleted(true);
    }

    /**
     * @inheritDoc
     */
    public function getEndSessionRequestUrl()
    {
        return UrlHelper::addQueryParams(
            $this->getEndSessionUrl(),
            [
                'endSessionAuthorizationRequestId' => $this->getRequestId()
            ]
        );
    }

    public function autoApproveAndProcess()
    {
        if ($this->getEndUserAuthorizationRequired()) {
            throw new InvalidCallException('Auto approve is only allowed if end-user authorization is not required.');
        }

        $this->setAuthorizationStatus(Oauth2BaseAuthorizationRequest::AUTHORIZATION_APPROVED);
        $this->processAuthorization();
    }

    public function getRequestCompletedRedirectUrl($ignoreApprovalStatus = false)
    {
        if (!$this->isApproved()) {
            return $this->getDeniedRedirectUrl();
        }

        $redirectUri = $this->getRedirectUri();

        if (!$redirectUri) {
            return $this->getDefaultRedirectUrl();
        }

        // Return the original `post_logout_redirect_uri` with the `state`
        return UrlHelper::addQueryParams($redirectUri, ['state' => $this->getState()]);
    }

    protected function getDefaultRedirectUrl()
    {
        return Yii::$app->getHomeUrl();
    }

    protected function getDeniedRedirectUrl()
    {
        return Yii::$app->getHomeUrl();
    }
}
