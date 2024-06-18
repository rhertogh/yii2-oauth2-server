<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\migrations;

use rhertogh\Yii2Oauth2Server\controllers\console\migrations\base\Oauth2BaseGenerateMigrationsAction;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2MigrationsController;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\console\migrations\Oauth2GenerateMigrationsActionInterface;
use rhertogh\Yii2Oauth2Server\migrations\base\Oauth2BaseMigration;
use yii\base\InvalidConfigException;

/**
 * @property Oauth2MigrationsController $controller
 */
class Oauth2GenerateMigrationsAction extends Oauth2BaseGenerateMigrationsAction implements Oauth2GenerateMigrationsActionInterface
{
    public function run()
    {
        $sourcePath = $this->getMigrationsSourcePath();
        $sourceNamespace = $this->getMigrationsSourceNamespace();
        $sourceMigrationClasses = $this->getSourceMigrationClasses($sourcePath, $sourceNamespace);

        return $this->generateMigrations($sourceMigrationClasses);
    }

    /**
     * @param string $migrationPath
     * @param string $namespace
     * @return class-string<Oauth2BaseMigration>[]
     */
    protected function getSourceMigrationClasses($migrationPath, $namespace)
    {
        if (!file_exists($migrationPath)) {
            throw new InvalidConfigException('Source migration path "' . $migrationPath . '" does not exist.');
        }

        $migrations = [];
        $handle = opendir($migrationPath);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $migrationPath . DIRECTORY_SEPARATOR . $file;
            if (preg_match('/^(Oauth2_\d{5}_.+Migration)\.php$/is', $file, $matches) && is_file($path)) {
                $migrations[] = $namespace . '\\' . $matches[1];
            }
        }
        closedir($handle);

        sort($migrations);

        return $migrations;
    }

    protected function getMigrationsSourcePath()
    {
        $reflector = new \ReflectionClass($this->controller->module);
        $moduleFile = $reflector->getFileName();

        return dirname($moduleFile) . DIRECTORY_SEPARATOR . 'migrations';
    }

    protected function getMigrationsSourceNamespace()
    {
        $reflector = new \ReflectionClass($this->controller->module);
        return $reflector->getNamespaceName() . '\\migrations';
    }
}
