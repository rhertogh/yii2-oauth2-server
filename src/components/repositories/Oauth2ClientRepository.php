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
    use Oauth2ModelRepositoryTrait {
        findModelByPk as traitFindModelByPk;
        findModelByIdentifier as traitFindModelByIdentifier;
    }

    /**
     * @inheritDoc
     * @return class-string<Oauth2ClientInterface>
     */
    public function getModelClass()
    {
        return Oauth2ClientInterface::class;
    }

    public function findModelByPk($pk)
    {
        /** @var Oauth2ClientInterface $client */
        $client = $this->traitFindModelByPk($pk);
        if ($client) {
            $client->setModule($this->_module);
        }
        return $client;
    }

    public function findModelByIdentifier($identifier)
    {
        /** @var Oauth2ClientInterface $client */
        $client = $this->traitFindModelByIdentifier($identifier);
        if ($client) {
            $client->setModule($this->_module);
        }
        return $client;
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
            && (
                !$client->isConfidential()
                || $client->validateSecret($clientSecret, $this->_module->getCryptographer())
            )
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

        return array_map(
            fn(Oauth2ClientInterface $client) => $client->setModule($this->_module),
            $className::find()
                ->andFilterWhere($filter)
                ->all(),
        );
    }
}
