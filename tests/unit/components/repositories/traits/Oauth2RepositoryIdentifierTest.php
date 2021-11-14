<?php

namespace Yii2Oauth2ServerTests\unit\components\repositories\traits;

use rhertogh\Yii2Oauth2Server\components\repositories\traits\Oauth2RepositoryIdentifierTrait;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\models\base\Oauth2BaseActiveRecord;
use Yii;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\repositories\traits\Oauth2RepositoryIdentifierTrait
 *
 */
class Oauth2RepositoryIdentifierTest extends TestCase
{
    public function testFindModelByIdentifierWithInvalidDependencyInjection()
    {
        $model = new class {
            use Oauth2RepositoryIdentifierTrait;

            public function getModelClass()
            {
                return Oauth2ActiveRecordInterface::class;
            }
        };

        Yii::$container->set(Oauth2ActiveRecordInterface::class, 'my-test-class');

        $this->expectExceptionMessage('my-test-class must implement ' . Oauth2ActiveRecordInterface::class);
        $model->findModelByIdentifier('test');
    }

    public function testFindModelByIdentifierWithInvalidModel()
    {
        $model = new class extends Oauth2BaseActiveRecord implements Oauth2ActiveRecordInterface {
            use Oauth2RepositoryIdentifierTrait;

            public function getModelClass()
            {
                return Oauth2ActiveRecordInterface::class;
            }

            public function findByIdentifier($identifier)
            {
                return new \stdClass();
            }

            public function hasAttribute($name)
            {
                return false; // Avoid database usage.
            }
            public function loadDefaultValues($skipIfSet = true)
            {
                // Avoid database usage.
            }
        };

        $modelClass = get_class($model);

        Yii::$container->set(Oauth2ActiveRecordInterface::class, $modelClass);

        $this->expectExceptionMessage(
            $modelClass . '::findByIdentifier() returns stdClass which must implement '
                . Oauth2ActiveRecordInterface::class
        );
        $model->findModelByIdentifier('test');
    }
}
