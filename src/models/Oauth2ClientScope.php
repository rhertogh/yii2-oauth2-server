<?php


namespace rhertogh\Yii2Oauth2Server\models;


use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientScopeInterface;

class Oauth2ClientScope extends base\Oauth2ClientScope implements Oauth2ClientScopeInterface
{
    /**
     * @inheritDoc
     */
    public function getAppliedByDefault()
    {
        $appliedByDefault = $this->applied_by_default;
        return $appliedByDefault !== null ? (int)$appliedByDefault : null;
    }

    /**
     * @inheritDoc
     */
    public function getRequiredOnAuthorization()
    {
        return $this->required_on_authorization;
    }
}
