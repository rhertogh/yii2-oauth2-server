<?php

namespace rhertogh\Yii2Oauth2Server\components\repositories\base;

use rhertogh\Yii2Oauth2Server\exceptions\UniqueTokenIdentifierConstraintViolationException;
use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2IdentifierInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ScopeRelationInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2TokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2RepositoryInterface;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

abstract class Oauth2BaseTokenRepository extends Oauth2BaseRepository
{
    /**
     * Create a new token based on the model class.
     * @return Oauth2TokenInterface
     * @throws InvalidConfigException
     * @see getModelClass()
     * @since 1.0.0
     */
    protected function getNewTokenInternally($config = [])
    {
        return Yii::createObject(ArrayHelper::merge(
            [
                'class' => $this->getModelClass()
            ],
            $config
        ));
    }

    /**
     * @param Oauth2ActiveRecordInterface|Oauth2IdentifierInterface|Oauth2ScopeRelationInterface $model
     * @throws InvalidConfigException
     * @throws UniqueTokenIdentifierConstraintViolationException
     * @throws \yii\db\Exception
     */
    protected function persistToken($model)
    {
        $expectedModelClass = $this->getModelClass();

        /** @var Oauth2ActiveRecordInterface|string $modelClass */
        $modelClass = get_class($model);

        if (!($model instanceof $expectedModelClass)) {
            throw new InvalidConfigException($modelClass . ' must implement ' . $expectedModelClass);
        }

        if ($model->identifierExists()) {
            throw UniqueTokenIdentifierConstraintViolationException::create(
                'A token of class ' . $modelClass
                    . ' with identifier "' . $model->getIdentifier() . '" already exists.'
            );
        }

        /** @var Connection $db */
        $db = $modelClass::getDb();

        $transaction = $db->beginTransaction();

        try {
            $model->persist();
            if ($model instanceof Oauth2ScopeRelationInterface) {
                $scopeRelation = $model->getScopesRelation();
                $scopeViaRelation = $scopeRelation->via;
                if (is_array($scopeViaRelation)) {
                    $scopeViaRelation = $scopeRelation->via[1];
                }
                $scopeRelationFkColumn = array_key_first($scopeViaRelation->link);
                $modelPk = $model->getPrimaryKey();

                /** @var Oauth2ActiveRecordInterface $scopeRelationClass */
                $scopeRelationClass = DiHelper::getClassName($model->getScopesRelationClassName());
                foreach ($model->getScopes() as $scope) {
                    if (!($scope instanceof Oauth2ScopeInterface)) {
                        throw new InvalidConfigException(
                            get_class($scope) . ' must implement ' . Oauth2ScopeInterface::class
                        );
                    }
                    /** @var Oauth2ActiveRecordInterface $scopeRelation */
                    $scopeRelation = Yii::createObject([
                        'class' => $scopeRelationClass,
                        $scopeRelationFkColumn => $modelPk,
                        'scope_id' => $scope->getPrimaryKey(),
                    ]);
                    $scopeRelation->persist();
                }
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param string $tokenIdentifier
     */
    protected function revokeToken($tokenIdentifier)
    {
        /** @var Oauth2TokenInterface $token */
        $token = $this->findModelByIdentifier($tokenIdentifier);
        if ($token) {
            $token->setRevokedStatus(true);
            $token->persist();
        }
    }

    /**
     * @param string $tokenIdentifier
     * @return bool
     */
    protected function isTokenRevoked($tokenIdentifier)
    {
        /** @var Oauth2TokenInterface  $token */
        $token = $this->findModelByIdentifier($tokenIdentifier);
        return empty($token) || $token->getRevokedStatus();
    }
}
