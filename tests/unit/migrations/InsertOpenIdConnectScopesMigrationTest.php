<?php

namespace Yii2Oauth2ServerTests\unit\migrations;

use rhertogh\Yii2Oauth2Server\migrations\Oauth2_00001_CreateOauth2TablesMigration;
use rhertogh\Yii2Oauth2Server\migrations\Oauth2_00002_InsertOpenIdConnectScopesMigration;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\migrations\_base\BaseMigrationTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\migrations\Oauth2_00002_InsertOpenIdConnectScopesMigration
 *
 */
class InsertOpenIdConnectScopesMigrationTest extends BaseMigrationTest
{
    public function getMigrationClass()
    {
        return Oauth2_00002_InsertOpenIdConnectScopesMigration::class;
    }

    public function dependsOnMigrations()
    {
        return [
            Oauth2_00001_CreateOauth2TablesMigration::class,
        ];
    }

    /**
     * @param $enableOpenIdConnect
     * @dataProvider generationIsActiveProvider
     */
    public function testGenerationIsActive($enableOpenIdConnect)
    {
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'enableOpenIdConnect' => $enableOpenIdConnect
                ],
            ],
        ]);

        /** @var string|Oauth2_00001_CreateOauth2TablesMigration $migrationClass */
        $migrationClass = $this->getMigrationClassWrapper($this->getMigrationClass());
        $this->assertEquals($enableOpenIdConnect, $migrationClass::generationIsActive(Oauth2Module::getInstance()));
    }

    /**
     * @see testGenerationIsActive
     * @return array[]
     */
    public function generationIsActiveProvider()
    {
        return [
            [true],
            [false],
        ];
    }
}
