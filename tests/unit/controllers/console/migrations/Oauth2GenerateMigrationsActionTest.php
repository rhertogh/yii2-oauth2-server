<?php

namespace Yii2Oauth2ServerTests\unit\controllers\console\migrations;

use rhertogh\Yii2Oauth2Server\controllers\console\migrations\Oauth2GenerateMigrationsAction;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2MigrationsController;
use rhertogh\Yii2Oauth2Server\migrations\Oauth2_00001_CreateOauth2TablesMigration;
use rhertogh\Yii2Oauth2Server\migrations\Oauth2_00002_InsertOpenIdConnectScopesMigration;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\console\ExitCode;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\console\migrations\Oauth2GenerateMigrationsAction
 */
class Oauth2GenerateMigrationsActionTest extends TestCase
{
    protected $runId = 'test';

    public function _before()
    {
        $this->runId = uniqid('test_' . gmdate('y-m-d_H-i-s') . '_');
    }

    protected function getMigrationsNamespace()
    {
        return 'runtime\\migrations\\' . $this->runId;
    }

    protected function getNamespacePath($namespace)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, Yii::getAlias('@' . str_replace('\\', '/', $namespace)));
    }

    protected function getMockController($config = [], $moduleConfig = [])
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => $moduleConfig,
            ],
        ]);

        return new class ('migrations', Oauth2Module::getInstance(), $config) extends Oauth2MigrationsController {
            public function stdout($string)
            {
                echo $string;
            }

            public function stderr($string)
            {
                echo $string;
            }
        };
    }

    /**
     * @dataProvider runOkProvider
     */
    public function testRunOK(
        $enableOpenIdConnect,
        $existingMigrations,
        $force,
        $expectedMigrations,
        $expectedDummyCount
    ) {
        $controller = $this->getMockController(
            [
                'interactive' => false,
                'force' => $force,
            ],
            [
                'migrationsNamespace' => $this->getMigrationsNamespace(),
                'enableOpenIdConnect' => $enableOpenIdConnect,
            ]
        );

        $action = new Oauth2GenerateMigrationsAction('generate', $controller);

        $this->injectExistingMigrations($action, $existingMigrations);

        $this->assertEquals(ExitCode::OK, $action->run());

        $migrationPath = $this->getNamespacePath($this->getMigrationsNamespace());
        $handle = opendir($migrationPath);
        $foundSources = [];
        $foundDummies = [];
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $migrationPath . DIRECTORY_SEPARATOR . $file;
            if (preg_match('/^(m(\d{6}_?\d{6})\D.*?)\.php$/is', $file, $matches) && is_file($path)) {
                $content = file_get_contents($path);
                if ($content == 'dummy') {
                    $foundDummies[$file] = true;
                } elseif (
                    preg_match(
                        '/class m\d{6}_?\d{6}_Oauth2_\d{5}_.+Migration extends (Oauth2_\d{5}_.+Migration)/i',
                        $content,
                        $matches
                    )
                ) {
                    $foundSources[$matches[1]] = true;
                }
            }
        }
        closedir($handle);

        // Ensure existing migrations were correctly injected.
        $this->assertEquals(
            $expectedDummyCount,
            count($foundDummies),
            'Different number of existing migrations injected than expected.'
        );

        $this->assertEquals(
            count($expectedMigrations),
            count($foundSources),
            'Different number of files generated than expected.'
        );

        foreach ($expectedMigrations as $expectedMigration) {
            $this->assertTrue(
                $foundSources[StringHelper::basename($expectedMigration)] ?? false,
                'Could not find generated class for ' . $expectedMigration
            );
        }
    }

    public function runOkProvider()
    {
        return [
            [
                true,
                [],
                false,
                [
                    Oauth2_00001_CreateOauth2TablesMigration::class,
                    Oauth2_00002_InsertOpenIdConnectScopesMigration::class,
                ],
                0,
            ],
            [
                false,
                [],
                false,
                [
                    Oauth2_00001_CreateOauth2TablesMigration::class,
                ],
                0,
            ],
            [
                true,
                [
                    Oauth2_00001_CreateOauth2TablesMigration::class,
                ],
                false,
                [
                    Oauth2_00002_InsertOpenIdConnectScopesMigration::class,
                ],
                1,
            ],
            [
                true,
                [
                    Oauth2_00001_CreateOauth2TablesMigration::class,
                    Oauth2_00002_InsertOpenIdConnectScopesMigration::class,
                ],
                false,
                [],
                2,
            ],
            [
                true,
                [
                    Oauth2_00001_CreateOauth2TablesMigration::class,
                    Oauth2_00002_InsertOpenIdConnectScopesMigration::class,
                ],
                true,
                [
                    Oauth2_00001_CreateOauth2TablesMigration::class,
                    Oauth2_00002_InsertOpenIdConnectScopesMigration::class,
                ],
                0,
            ],
        ];
    }

    public function testRunEmptyMigrationsNamespace()
    {
        $controller = $this->getMockController();
        $action = new Oauth2GenerateMigrationsAction('generate', $controller);

        $this->expectExceptionMessage('Oauth2Module::$migrationsNamespace must be set.');
        $action->run();
    }

    public function testSourceMigrationClassesInvalidPath()
    {
        $controller = $this->getMockController([], ['migrationsNamespace' => $this->getMigrationsNamespace()]);
        $action = new Oauth2GenerateMigrationsAction('generate', $controller);

        $this->expectExceptionMessage('Source migration path "nope" does not exist.');
        $this->callInaccessibleMethod($action, 'getSourceMigrationClasses', ['nope', '']);
    }

    public function testRunWriteFail()
    {
        $controller = $this->getMockController(
            [
                'interactive' => false,
            ],
            [
                'migrationsNamespace' => $this->getMigrationsNamespace(),
            ]
        );

        $action = new class ('generate', $controller) extends Oauth2GenerateMigrationsAction {
            protected function writeFile($file, $data)
            {
                return false;
            }
        };

        $this->assertEquals(ExitCode::IOERR, $action->run());
    }

    protected function injectExistingMigrations($action, $migrations)
    {
        foreach ($migrations as $migration) {
            $name = $this->callInaccessibleMethod(
                $action,
                'generateNewMigrationClassName',
                [$this->getMigrationsNamespace(), StringHelper::basename($migration)]
            );
            $migrationsPath = $this->getNamespacePath(StringHelper::dirname($name));
            $file = $migrationsPath . DIRECTORY_SEPARATOR . StringHelper::basename($name) . '.php';

            FileHelper::createDirectory($migrationsPath);
            file_put_contents($file, 'dummy');
        }
    }
}
