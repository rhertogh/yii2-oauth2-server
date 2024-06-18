<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console;

use rhertogh\Yii2Oauth2Server\controllers\console\base\Oauth2BaseConsoleController;
use rhertogh\Yii2Oauth2Server\controllers\console\migrations\Oauth2GenerateImportMigrationAction;
use rhertogh\Yii2Oauth2Server\controllers\console\migrations\Oauth2GenerateMigrationsAction;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\console\Oauth2MigrationsControllerInterface;
use yii\helpers\ArrayHelper;

class Oauth2MigrationsController extends Oauth2BaseConsoleController implements Oauth2MigrationsControllerInterface
{
    /**
     * Name of the database component.
     * @var string
     */
    public $db = 'db';

    /**
     * Force generation of existing migrations.
     * @var bool
     */
    public $force = false;

    /**
     * @inheritDoc
     */
    public function options($actionID)
    {
        $options = ArrayHelper::merge(parent::options($actionID), [
            'force',
        ]);

        if ($actionID === 'generate-import') {
            $options[] = 'db';
        }

        return $options;
    }

    /**
     * @inheritDoc
     */
    public function optionAliases()
    {
        return ArrayHelper::merge(parent::optionAliases(), [
            'f' => 'force',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function actions()
    {
        return [
            'generate' => Oauth2GenerateMigrationsAction::class,
            'generate-import' => Oauth2GenerateImportMigrationAction::class,
        ];
    }
}
