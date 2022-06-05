<?php

namespace rhertogh\Yii2Oauth2Server\models;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2ActiveRecordIdTrait;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2EntityIdentifierTrait;

class Oauth2Scope extends base\Oauth2Scope implements Oauth2ScopeInterface
{
    use Oauth2ActiveRecordIdTrait;
    use Oauth2EntityIdentifierTrait;

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange] // Suppress "return type should be compatible" warning.
    public function jsonSerialize()
    {
        return $this->getIdentifier();
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @inheritDoc
     */
    public function getAuthorizationMessage()
    {
        return $this->authorization_message;
    }

    /**
     * @inheritDoc
     */
    public function getAppliedByDefault()
    {
        return (int)$this->applied_by_default;
    }

    /**
     * @inheritDoc
     */
    public function getRequiredOnAuthorization()
    {
        return $this->required_on_authorization === null ? null : (bool)$this->required_on_authorization;
    }

    /**
     * @inheritDoc
     */
    public function getClientScope($clientId)
    {
        if ($this->isRelationPopulated('clientScopes')) {
            foreach ($this->clientScopes as $clientScope) {
                if ($clientScope->client_id === $clientId) {
                    return $clientScope;
                }
            }

            return null;
        }

        return $this->getClientScopes()
            ->andWhere(['client_id' => $clientId])
            ->one();
    }
}
