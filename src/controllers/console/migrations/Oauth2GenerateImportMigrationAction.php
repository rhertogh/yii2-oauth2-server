<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\migrations;

use rhertogh\Yii2Oauth2Server\controllers\console\migrations\base\Oauth2BaseGenerateMigrationsAction;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2MigrationsController;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\console\migrations\Oauth2GenerateImportMigrationActionInterface;
use rhertogh\Yii2Oauth2Server\migrations\import\Oauth2_FilshYii2Oauth2ServerImportMigration;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\console\ExitCode;
use yii\db\Connection;

/**
 * @property Oauth2MigrationsController $controller
 */
class Oauth2GenerateImportMigrationAction extends Oauth2BaseGenerateMigrationsAction implements Oauth2GenerateImportMigrationActionInterface
{
    /**
     * Generate a migration to import data from another Oauth server.
     */
    public function run($origin)
    {
        $imports = [
            'filsh' => [$this, 'generatedFilshImport'],
        ];

        if (!array_key_exists($origin, $imports)) {
            throw new InvalidArgumentException('Unknown import origin "' . $origin . '", available origins: '
                . implode(', ', array_keys($imports)) . '.');
        }

        return call_user_func($imports[$origin]);
    }

    /**
     * Generate a migration to import data from https://github.com/filsh/yii2-oauth2-server
     * @return int
     */
    protected function generatedFilshImport()
    {
        $controller = $this->controller;

        if (!$controller->confirm('Generate migration for "filsh/yii2-oauth2-server" data import?', true)) {
            return ExitCode::OK;
        }

        $db = Yii::$app->get($controller->db);
        if (!($db instanceof Connection)) {
            throw new InvalidConfigException(get_class($controller)
                . '::$db must reference an Yii::$app component that is a ' . Connection::class);
        }

        $defaultClientTable = '{{%oauth_clients}}';
        if (!$db->getTableSchema($defaultClientTable)) {
            $defaultClientTable = null;
        }
        $clientTable = $this->controller->prompt('Original clients table name?', [
            'required' => true,
            'default' => $defaultClientTable,
            'validator' => function ($input, &$error) use ($db) {
                if (!$db->getTableSchema($input)) {
                    $error = 'Table "' . $input . '" not found.';
                    return false;
                }
                return true;
            },
        ]);

        return $this->generateMigrations([
            Oauth2_FilshYii2Oauth2ServerImportMigration::class => [
                'clientTable' => $clientTable,
            ],
        ]);
    }
}
