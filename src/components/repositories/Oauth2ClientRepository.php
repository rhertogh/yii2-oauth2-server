<?php

namespace rhertogh\Yii2Oauth2Server\components\repositories;

use rhertogh\Yii2Oauth2Server\components\repositories\base\Oauth2BaseRepository;
use rhertogh\Yii2Oauth2Server\components\repositories\traits\Oauth2ModelRepositoryTrait;
use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2ClientRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use yii\base\InvalidConfigException;

class Oauth2ClientRepository extends Oauth2BaseRepository implements Oauth2ClientRepositoryInterface
{
    use Oauth2ModelRepositoryTrait;

    /**
     * @inheritDoc
     * @return class-string<Oauth2ClientInterface>
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
        /** @var Oauth2ClientInterface $client */
        $client = $this->findModelByIdentifier($clientIdentifier);
        if ($client) {
            $client->setRedirectUriEnvVarConfig($this->_module->clientRedirectUriEnvVarConfig);
        }
        return $client;
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
            && (!$client->isConfidential() || $client->validateSecret($clientSecret, $this->_module->getCryptographer()))
        ) {
            return true;
        }

        return false;
    }

    public function getAllClients($filter = [])
    {
        $class = $this->getModelClass();
        /** @var class-string<Oauth2ClientInterface> $className */
        $className = DiHelper::getValidatedClassName($class);

        return $className::find()
            ->andFilterWhere($filter)
            ->all();
    }
}
