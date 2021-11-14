<?php

namespace rhertogh\Yii2Oauth2Server\models\traits;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ClientQueryInterface;
use yii\base\InvalidConfigException;

trait Oauth2ClientRelationTrait
{
    /**
     * Wrapper for parent's getClient() relation to avoid name conflicts
     * @return Oauth2ClientQueryInterface
     * @since 1.0.0
     */
    public function getClientRelation()
    {
        return parent::getClient();
    }

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        if ($name === 'client_id' && $this->isRelationPopulated('clientRelation')) {
            unset($this['clientRelation']);
        }
        parent::__set($name, $value);
    }

    /**
     * @inheritDoc
     */
    public function setAttribute($name, $value)
    {
        if ($name === 'client_id' && $this->isRelationPopulated('clientRelation')) {
            unset($this['clientRelation']);
        }
        parent::setAttribute($name, $value);
    }

    /**
     * @inheritDoc
     */
    public function setClient(ClientEntityInterface $client)
    {
        if (!($client instanceof Oauth2ClientInterface)) {
            throw new InvalidConfigException(get_class($client) . ' must implement ' . Oauth2ClientInterface::class);
        }

        $this->client_id = $client->getPrimaryKey();
        $this->populateRelation('clientRelation', $client);
    }

    /**
     * Get the client for this model.
     * @return Oauth2ClientInterface
     * @since 1.0.0
     */
    public function getClient()
    {
        return $this->clientRelation;
    }
}
