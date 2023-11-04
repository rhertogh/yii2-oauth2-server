<?php

namespace Yii2Oauth2ServerTests\Helper;

use Codeception\Module\Yii2;
use Codeception\TestInterface;

class Yii2Module extends Yii2
{
    public function _before(TestInterface $test)
    {
        parent::_before($test);

        $this->client->setServerParameter('REMOTE_ADDR', '127.0.0.1');
    }

    /**
     * @inheritdoc
     *
     * Overwritten in order to ignore default `Codeception\Lib\Connector\Yii2\FixturesStore`.
     */
    public function haveFixtures($fixtures)
    {
        if (empty($fixtures)) {
            return;
        }
        $fixturesStore = new FixturesStore($fixtures); // <- use own FixturesStore
        $fixturesStore->unloadFixtures();
        $fixturesStore->loadFixtures();
        $this->loadedFixtures[] = $fixturesStore;
    }
}
