<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yii2Oauth2ServerTests\unit;

use Yii;
use yii\helpers\ArrayHelper;
use Yii2Oauth2ServerTests\_helpers\fixtures\FullDbFixture;
use Yii2Oauth2ServerTests\Helper\Yii2Module;

abstract class DatabaseTestCase extends TestCase
{
    /**
     * @throws \yii\db\Exception
     */
    public function _fixtures()
    {
        return [
            'db' => FullDbFixture::class,
        ];
    }

    /**
     * @param array $config
     * @return array
     */
    protected function getMockBaseAppConfig($config = []): array
    {
        /** @var Yii2Module $yii2Module */
        $yii2Module = $this->getModule(Yii2Module::class);
        /** @var FullDbFixture $dbFixture */
        $dbFixture = $yii2Module->grabFixture('db');

        //$dbConfig = InitDbFixture::getDbConfig($this->driverName);
        return ArrayHelper::merge(
            parent::getMockBaseAppConfig(),
            [
                'components' => [
                    'db' => $dbFixture->db,
                ],
            ],
            $config,
        );
    }

    protected function _before()
    {
        parent::_before();
        static::mockConsoleApplication();
    }

    protected function _after()
    {
        if (Yii::$app->db) {
            Yii::$app->db->close();
        }

        parent::_after();
    }
}
