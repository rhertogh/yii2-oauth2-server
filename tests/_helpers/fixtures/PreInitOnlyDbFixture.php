<?php

namespace Yii2Oauth2ServerTests\_helpers\fixtures;

class PreInitOnlyDbFixture extends BaseDbFixture
{
    protected function createDbFixtures()
    {
        DatabaseFixtures::createDbFixtures($this->driverName, true, false, false);
    }
}
