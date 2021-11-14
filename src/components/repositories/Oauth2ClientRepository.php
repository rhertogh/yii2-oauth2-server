<?php

namespace rhertogh\Yii2Oauth2Server\components\repositories;

use rhertogh\Yii2Oauth2Server\components\repositories\base\Oauth2BaseRepository;
use rhertogh\Yii2Oauth2Server\components\repositories\traits\Oauth2RepositoryIdentifierTrait;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2ClientRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use yii\base\InvalidConfigException;

class Oauth2ClientRepository extends Oauth2BaseRepository implements Oauth2ClientRepositoryInterface
{
    use Oauth2RepositoryIdentifierTrait;

    /**
     * @inheritDoc
     * @return Oauth2ClientInterface|string
     */
    public function getModelClass()
    {
        return Oauth2ClientInterface::class;
    }

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function getClientEntity($clientIdentifier)
    {
        return $this->findModelByIdentifier($clientIdentifier);
    }

    /**
     * @inheritDoc
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        /** @var Oauth2ClientInterface $client */
        $client = $this->findModelByIdentifier($clientIdentifier);

        if (
            $client
            && $client->isEnabled()
            && $client->validateGrantType($grantType)
            && (!$client->isConfidential() || $client->validateSecret($clientSecret, $this->_module->getEncryptor()))
        ) {
            return true;
        }

        return false;
    }
}
