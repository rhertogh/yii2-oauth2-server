<?php

namespace Yii2Oauth2ServerTests\_helpers\fixtures;

class FullDbFixture extends BaseDbFixture
{
    protected function createDbFixtures()
    {
        DatabaseFixtures::createDbFixtures($this->driverName);
    }
}
