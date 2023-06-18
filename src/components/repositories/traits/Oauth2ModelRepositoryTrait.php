<?php

namespace rhertogh\Yii2Oauth2Server\components\repositories\traits;

use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2IdentifierInterface;
use yii\base\InvalidConfigException;

trait Oauth2ModelRepositoryTrait
{
    /**
     * @inheritDoc
     * @return class-string<Oauth2ActiveRecordInterface>
     */
    abstract public function getModelClass();

    public function findModelByPk($pk)
    {
        $class = $this->getModelClass();
        /** @var class-string<Oauth2ActiveRecordInterface> $className */
        $className = DiHelper::getValidatedClassName($class);

        $result = $className::findByPk($pk);
        if ($result !== null && !($result instanceof $class)) {
            throw new InvalidConfigException(
                $className . '::findByPk() returns '
                . get_class($result) . ' which must implement ' . $class
            );
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @param string $identifier
     * @return Oauth2IdentifierInterface
     * @throws InvalidConfigException
     */
    public function findModelByIdentifier($identifier)
    {
        $class = $this->getModelClass();
        /** @var class-string<Oauth2IdentifierInterface> $className */
        $className = DiHelper::getValidatedClassName($class);

        $result = $className::findByIdentifier($identifier);
        if ($result !== null && !($result instanceof $class)) {
            throw new InvalidConfigException(
                $className . '::findByIdentifier() returns '
                    . get_class($result) . ' which must implement ' . $class
            );
        }

        return $result;
    }
}
