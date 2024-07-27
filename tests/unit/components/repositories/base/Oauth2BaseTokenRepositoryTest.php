<?php

namespace Yii2Oauth2ServerTests\unit\components\repositories\base;

use rhertogh\Yii2Oauth2Server\components\repositories\base\Oauth2BaseTokenRepository;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2IdentifierInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ScopeRelationInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\models\base\Oauth2BaseActiveRecord;
use rhertogh\Yii2Oauth2Server\models\Oauth2AuthCodeScope;
use rhertogh\Yii2Oauth2Server\models\Oauth2Scope;
use rhertogh\Yii2Oauth2Server\models\queries\Oauth2AuthCodeScopeQuery;
use rhertogh\Yii2Oauth2Server\models\queries\Oauth2ScopeQuery;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2EntityIdentifierTrait;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2ScopesRelationTrait;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\repositories\base\Oauth2BaseTokenRepository
 *
 */
class Oauth2BaseTokenRepositoryTest extends DatabaseTestCase
{
    public function testPersistTokenWithInvalidModel()
    {
        $this->expectExceptionMessage('stdClass must implement ' . Oauth2ActiveRecordInterface::class);
        $this->getMockBaseTokenRepository()->persistTokenWrapper(Oauth2ActiveRecordInterface::class, new \stdClass());
    }

    public function testPersistTokenWithInvalidScopes()
    {
        $model = new class extends Oauth2BaseActiveRecord implements
           Oauth2IdentifierInterface,
            Oauth2ScopeRelationInterface
        {
            use Oauth2EntityIdentifierTrait;
            use Oauth2ScopesRelationTrait;

            public function persist($runValidation = true, $attributeNames = null)
            {
                return true; // Persistence of main model is tested in implementation classes.
            }
            public function identifierExists()
            {
                return false; // Existence of main model is tested in implementation classes.
            }

            public function hasAttribute($name)
            {
                return false; // Avoid database usage.
            }
            public function loadDefaultValues($skipIfSet = true)
            {
                // Avoid database usage.
            }

            public function getScopesRelationClassName()
            {
                return null;
            }

            public function getPrimaryKey($asArray = false)
            {
                return null;
            }

            public function getScopes()
            {
                return [new \stdClass()];
            }

            public function getScopesRelation()
            {
                return new Oauth2ScopeQuery(Oauth2Scope::class, [
                    'primaryModel' => $this,
                    'link' => ['id' => 'scope_id'],
                    'multiple' => true,
                    'via' => ['authCodeScopes', new Oauth2AuthCodeScopeQuery(Oauth2AuthCodeScope::class, [
                        'primaryModel' => $this,
                        'link' => ['auth_code_id' => 'id'],
                        'multiple' => true,
                    ]), false]
                ]);
            }
        };

        $this->expectExceptionMessage('stdClass must implement ' . Oauth2ScopeInterface::class);
        $this->getMockBaseTokenRepository()->persistTokenWrapper(Oauth2ActiveRecordInterface::class, $model);
    }

    protected function getMockBaseTokenRepository()
    {
        return new class extends Oauth2BaseTokenRepository {
            public function getModelClass()
            {
                return Oauth2ActiveRecordInterface::class;
            }

            public function persistTokenWrapper($class, $model)
            {
                $this->persistToken($model);
            }

            public function findModelByPk($pk)
            {
                // Not used at the moment.
            }

            public function findModelByIdentifier($identifier)
            {
                // Not used at the moment.
            }

            public function findModelByPkOrIdentifier($pkOrIdentifier)
            {
                // Not used at the moment.
            }
        };
    }
}
