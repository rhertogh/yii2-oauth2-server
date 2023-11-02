<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\migrations\base;

use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2MigrationsController;
use rhertogh\Yii2Oauth2Server\migrations\base\Oauth2BaseMigration;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\console\controllers\BaseMigrateController;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;

/**
 * @property Oauth2MigrationsController $controller
 */
abstract class Oauth2BaseGenerateMigrationsAction extends Action
{
    /**
     * Template to use for the generated migrations.
     * @var string
     */
    public $templateFile = __DIR__ . DIRECTORY_SEPARATOR . 'generate' . DIRECTORY_SEPARATOR . 'migration.php';

    /**
     * @param class-string<Oauth2BaseMigration>[]|array<class-string<Oauth2BaseMigration>, array> $sourceMigrationClasses
     * @return int
     * @throws InvalidConfigException
     * @throws \yii\base\Exception
     */
    protected function generateMigrations($sourceMigrationClasses)
    {
        $module = $this->controller->module;
        $migrationsNamespace = $module->migrationsNamespace;

        if (empty($migrationsNamespace)) {
            throw new InvalidConfigException('Oauth2Module::$migrationsNamespace must be set.');
        }

        $migrationsPath = $this->getNamespacePath($migrationsNamespace);
        $existingMigrations = $this->getExistingMigrations($migrationsPath);

        $generateMigrations = [];
        foreach ($sourceMigrationClasses as $sourceMigrationClass => $config) {
            if (is_int($sourceMigrationClass)) {
                $sourceMigrationClass = $config;
                $config = [];
            }
            $sourceMigrationName = StringHelper::basename($sourceMigrationClass);
            $wrapperName = $sourceMigrationName . 'Wrapper';
            /** @var string|Oauth2BaseMigration $sourceMigrationWrapper */
            $sourceMigrationWrapper = __NAMESPACE__ . '\\' . $wrapperName;
            if (!class_exists($sourceMigrationWrapper, false)) {
                eval(
                    'namespace ' . __NAMESPACE__ . ';'
                    . ' class ' . $wrapperName . ' extends \\' . $sourceMigrationClass . ' {}'
                );
            }

            if (
                $sourceMigrationWrapper::generationIsActive($module)
                && ($this->controller->force || !array_key_exists($sourceMigrationName, $existingMigrations))
            ) {
                if ($this->controller->force && array_key_exists($sourceMigrationName, $existingMigrations)) {
                    $generateClass = $migrationsNamespace . '\\' . $existingMigrations[$sourceMigrationName];
                } else {
                    $generateClass = $this->generateNewMigrationClassName($migrationsNamespace, $sourceMigrationName);
                }

                $file = $migrationsPath . DIRECTORY_SEPARATOR . StringHelper::basename($generateClass) . '.php';
                $content = $this->generateMigrationSourceCode([
                    'class' => $generateClass,
                    'sourceClass' => $sourceMigrationClass,
                    'config' => $config,
                ]);

                if (
                    !file_exists($file)
                    || file_get_contents($file) !== $content
                ) {
                    $generateMigrations[$file] = $content;
                }
            }
        }

        $applyInfo = $this->generateApplyInfo($migrationsNamespace);

        if (empty($generateMigrations)) {
            $this->controller->stdout(
                'No new to generate. However, they might still need to be applied.' . PHP_EOL . $applyInfo,
                Console::FG_GREEN
            );

            return ExitCode::OK;
        }

        $n = count($generateMigrations);
        $this->controller->stdout(
            "There " . ($n === 1 ? 'is' : 'are') . " $n " . ($n === 1 ? 'migration' : 'migrations')
                . ' to generate:' . PHP_EOL,
            Console::FG_YELLOW
        );
        foreach ($generateMigrations as $file => $content) {
            $this->controller->stdout("\t$file ");
            if (file_exists($file)) {
                $this->controller->stdout('[update]', Console::BG_YELLOW);
            } else {
                $this->controller->stdout('[new]', Console::BG_GREEN);
            }
            $this->controller->stdout(PHP_EOL);
        }
        $this->controller->stdout(PHP_EOL);

        if ($this->controller->confirm('Generate new migration?', true)) {
            FileHelper::createDirectory($migrationsPath);
            FileHelper::changeOwnership($migrationsPath, $module->migrationsFileOwnership);

            foreach ($generateMigrations as $file => $content) {
                if (!$this->writeFile($file, $content)) {
                    $this->controller->stdout("Failed to create new migration $file." . PHP_EOL, Console::FG_RED);
                    return ExitCode::IOERR;
                }

                FileHelper::changeOwnership($file, $module->migrationsFileOwnership, $module->migrationsFileMode);
            }

            $this->controller->stdout(
                "Successfully generated $n " . ($n === 1 ? 'migration' : 'migrations') . '.'  . PHP_EOL . $applyInfo,
                Console::FG_GREEN
            );
        }

        return ExitCode::OK;
    }

    protected function writeFile($file, $data)
    {
        return file_put_contents($file, $data, LOCK_EX) !== false;
    }

    protected function getNamespacePath($namespace)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, Yii::getAlias('@' . str_replace('\\', '/', $namespace)));
    }

    protected function getExistingMigrations($migrationPath)
    {
        if (!file_exists($migrationPath)) {
            return [];
        }

        $migrations = [];
        $handle = opendir($migrationPath);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $migrationPath . DIRECTORY_SEPARATOR . $file;
            if (preg_match('/^(m\d{12}.*_(Oauth2_\d{5}_.+Migration).*?)\.php$/is', $file, $matches) && is_file($path)) {
                $migrations[$matches[2]] = $matches[1];
            }
        }
        closedir($handle);

        ksort($migrations);

        return $migrations;
    }

    protected function generateMigrationSourceCode($params)
    {
        return $this->controller->renderFile(Yii::getAlias($this->templateFile), $params);
    }

    /**
     * @param string $migrationsNamespace
     * @param string $sourceMigrationName
     * @return string
     */
    protected function generateNewMigrationClassName($migrationsNamespace, $sourceMigrationName)
    {
        return $migrationsNamespace
            . '\\' . 'M' . gmdate('ymdHis')
            . $this->controller->module->migrationsPrefix . '_' . $sourceMigrationName;
    }

    /**
     * @param string $migrationsNamespace
     * @return string
     */
    protected function generateApplyInfo($migrationsNamespace)
    {
        $migrateControllerId = 'migrate';
        $oauth2MigrationNameSpaceFound = false;
        foreach (Yii::$app->controllerMap as $controllerId => $controllerConfig) {
            if (
                !is_array($controllerConfig)
                || empty($controllerConfig['class'])
                || !is_a($controllerConfig['class'], BaseMigrateController::class, true)
            ) {
                continue;
            }
            $migrateControllerId = $controllerId;
            if (
                !empty($controllerConfig['migrationNamespaces'])
                && in_array($migrationsNamespace, $controllerConfig['migrationNamespaces'])
            ) {
                $oauth2MigrationNameSpaceFound = true;
                break;
            }
        }

        if (!$oauth2MigrationNameSpaceFound) {
            $applyInfo = 'Add the "' . addslashes($migrationsNamespace) . '" namespace to `$migrationNamespaces` of the'
                . ' migration controller'
                . ' (https://www.yiiframework.com/doc/guide/2.0/en/db-migrations#namespaced-migrations)'
                . ' and run ';
        } else {
            $applyInfo = 'Run ';
        }
        $applyInfo .= 'the`yii ' . $migrateControllerId . '/up` command to apply them.' . PHP_EOL;

        return $applyInfo;
    }
}
