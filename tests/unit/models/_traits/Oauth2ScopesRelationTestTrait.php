<?php

namespace Yii2Oauth2ServerTests\unit\models\_traits;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\ScopeTrait;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2ScopeQueryInterface;
use Yii;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BaseModelTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BasePhpUnitTestCaseTrait;

trait Oauth2ScopesRelationTestTrait
{
    use Oauth2BaseModelTestTrait;
    use Oauth2BasePhpUnitTestCaseTrait;

    public function testGetScopesRelation()
    {
        $model = $this->getMockModel();
        $scopesRelation = $model->getScopesRelation();
        $this->assertInstanceOf(Oauth2ScopeQueryInterface::class, $scopesRelation);
    }

    public function testGetSetAddScopes()
    {
        /** @var Oauth2AuthCodeInterface|Oauth2AccessTokenInterface $model */
        $model = $this->getMockModel();

        /** @var Oauth2ScopeInterface[] $scopes */
        $scopes = [
            Yii::createObject([
                'class' => Oauth2ScopeInterface::class,
                'id' => 1,
            ]),
            Yii::createObject([
                'class' => Oauth2ScopeInterface::class,
                'id' => 2,
            ]),
        ];
        $this->assertEmpty($model->getScopes());
        $model->setScopes($scopes);
        $this->assertEquals($scopes, $model->getScopes());

        /** @var Oauth2ScopeInterface $additionalScope */
        $additionalScope = Yii::createObject([
            'class' => Oauth2ScopeInterface::class,
            'id' => 3,
        ]);

        $scopes[] = $additionalScope;
        $model->addScope($additionalScope);
        $this->assertEquals($scopes, $model->getScopes());
    }

    public function testAddInvalidScope()
    {
        /** @var Oauth2AuthCodeInterface|Oauth2AccessTokenInterface $model */
        $model = $this->getMockModel();

        $mockScope = new class implements ScopeEntityInterface {
            use ScopeTrait;

            public function getIdentifier()
            {
                return null;
            }
        };

        $this->expectExceptionMessage(get_class($mockScope) . ' must implement ' . Oauth2ScopeInterface::class);
        $model->addScope($mockScope);
    }
}
