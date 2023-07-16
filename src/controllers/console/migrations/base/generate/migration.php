<?php

/**
 * This view is used by src/controllers/console/migrations/Oauth2GenerateAction.php.
 *
 * The following variables are available in this view:
 */

/* @var $class string the new migration class name including namespace */
/* @var $sourceClass string the source class to extend */
/* @var $config array the source class to extend */

use yii\helpers\StringHelper;
use yii\helpers\VarDumper;

echo "<?php\n";
?>

namespace <?= StringHelper::dirname($class) ?>;

use <?= $sourceClass ?>;

/**
 * Class <?= $class . "\n" ?>
 *
 * phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
class <?= StringHelper::basename($class) ?> extends <?= StringHelper::basename($sourceClass) ?>

{
    /**
     * Wrapper class for <?= $sourceClass ?>,
     * no further implementation required.
     */
<?php
    if ($config) {
        $configCode = ltrim(preg_replace('/^/m', '    ', VarDumper::export($config)));
        echo PHP_EOL . '    public $config = ' . $configCode . ';' . PHP_EOL;
    }
?>
}
