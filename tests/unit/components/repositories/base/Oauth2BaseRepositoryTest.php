<?php
namespace Yii2Oauth2ServerTests\unit\components\repositories\base;

use rhertogh\Yii2Oauth2Server\components\repositories\base\Oauth2BaseRepository;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\repositories\base\Oauth2BaseRepository
 *
 */
class Oauth2BaseRepositoryTest extends DatabaseTestCase
{
    public function testSetModule()
    {
        $this->mockConsoleApplication();
        $module = Oauth2Module::getInstance();
        $baseRepository = $this->getMockBaseRepository();
        $baseRepository->setModule($module);
        $this->assertEquals($module, $this->getInaccessibleProperty($baseRepository, '_module'));
    }

    protected function getMockBaseRepository()
    {
        return new class extends Oauth2BaseRepository {
            public function getModelClass() {
                return Oauth2ActiveRecordInterface::class;
            }
        };
    }
}
