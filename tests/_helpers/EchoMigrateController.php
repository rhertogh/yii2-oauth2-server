<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yii2Oauth2ServerTests\_helpers;

use Yii;
use yii\console\controllers\MigrateController;
use yii\helpers\StringHelper;

/**
 * MigrateController that writes output via echo instead of using output stream. Allows us to buffer it.
 */
class EchoMigrateController extends MigrateController
{
    /**
     * {@inheritdoc}
     */
    public function stdout($string)
    {
        echo $string;
    }

    /**
     * Creates a new migration instance.
     * @param string $class the migration class name
     * @return \yii\db\Migration the migration instance
     */
    protected function createMigration($class)
    {
        $migrationClassName = StringHelper::basename($class);
        $wrapperName = $migrationClassName . 'Wrapper';
        $wrapperClass = __NAMESPACE__ . '\\' . $wrapperName;
        if (!class_exists($wrapperClass, false)) {
            eval('namespace ' . __NAMESPACE__ . '; class ' . $wrapperName . ' extends \\' . $class . ' {}');
        }
        return parent::createMigration($wrapperClass);
    }

    /**
     * Returns the migrations that are not applied.
     * @return array list of new migrations
     */
    protected function getNewMigrations()
    {
        $applied = [];
        foreach ($this->getMigrationHistory(null) as $class => $time) {
            $applied[trim($class, '\\')] = true;
        }

        $migrationPaths = [];
        if (is_array($this->migrationPath)) {
            foreach ($this->migrationPath as $path) {
                $migrationPaths[] = [$path, ''];
            }
        } elseif (!empty($this->migrationPath)) {
            $migrationPaths[] = [$this->migrationPath, ''];
        }
        foreach ($this->migrationNamespaces as $namespace) {
            $migrationPaths[] = [$this->getNamespacePath($namespace), $namespace];
        }

        $migrations = [];
        foreach ($migrationPaths as $item) {
            list($migrationPath, $namespace) = $item;
            if (!file_exists($migrationPath)) {
                continue;
            }
            $handle = opendir($migrationPath);
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $migrationPath . DIRECTORY_SEPARATOR . $file;
                if (preg_match('/^(Oauth2_(\d{5})_.*?)\.php$/is', $file, $matches) && is_file($path)) {
                    $class = $matches[1];
                    if (!empty($namespace)) {
                        $class = $namespace . '\\' . $class;
                    }
                    $time = str_replace('_', '', $matches[2]);
                    if (!isset($applied[$class])) {
                        $migrations[$time . '\\' . $class] = $class;
                    }
                }
            }
            closedir($handle);
        }
        ksort($migrations);

        return array_values($migrations);
    }

    /**
     * Returns the file path matching the give namespace.
     * @param string $namespace namespace.
     * @return string file path.
     * @since 2.0.10
     */
    protected function getNamespacePath($namespace)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, Yii::getAlias('@' . str_replace('\\', '/', $namespace)));
    }
}
