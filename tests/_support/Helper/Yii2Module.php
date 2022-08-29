<?php

namespace Yii2Oauth2ServerTests\Helper;

use Codeception\Module\Yii2;

class Yii2Module extends Yii2
{
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
