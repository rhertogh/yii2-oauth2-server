<?php

namespace Yii2Oauth2ServerTests\unit\traits;

use rhertogh\Yii2Oauth2Server\filters\auth\Oauth2HttpBearerAuth;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use rhertogh\Yii2Oauth2Server\traits\models\Oauth2UserIdentityTrait;
use Yii;
use yii\base\Module;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\traits\models\Oauth2UserIdentityTrait
 */
class Oauth2UserIdentityTraitTest extends DatabaseTestCase
{
    public function testFindIdentityByAccessToken()
    {
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'resourceServerAccessTokenRevocationValidation' => false, // Token revocation validation is tested during functional testing
                ]
            ]
        ]);
        Yii::$app->request->headers->set('Authorization', 'Bearer ' . $this->validAccessToken);
        $module = Oauth2Module::getInstance();

        $module->validateAuthenticatedRequest();
        /** @var Oauth2UserIdentityTrait $modelClass */
        $modelClass = get_class(new class {
            use Oauth2UserIdentityTrait;

            public function getId()
            {
                return 0;
            } // not used
        });

        $identity = $modelClass::findIdentityByAccessToken($this->validAccessToken, Oauth2HttpBearerAuth::class);
        $this->assertEquals(123, $identity->getIdentifier());
    }

    public function testFindIdentityByAccessTokenCustomModule()
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2-test' => get_class(new class ('oauth2-test') extends Module {
                    public function findIdentityByAccessToken($token, $type)
                    {
                        return new class {
                            public $id = 999;
                        };
                    }
                })
            ],
        ]);

        /** @var Oauth2UserIdentityTrait|string $modelClass */
        $modelClass = get_class(new class {
            public static $oauth2ModuleName = 'oauth2-test';
            use Oauth2UserIdentityTrait;

            public function getId()
            {
                return 0; // not used
            }
        });

        $user = $modelClass::findIdentityByAccessToken('test.token', 'test.type');
        $this->assertEquals(999, $user->id);
    }

    public function testGetIdentifier()
    {
        $model = new class {
            use Oauth2UserIdentityTrait;

            public function getId()
            {
                return 123;
            }
        };

        $this->assertEquals($model->getId(), $model->getIdentifier());
    }
}
