<?php

// Workaround for PhpStorm bug. https://stackoverflow.com/a/55040543/7702252
use yii\helpers\Json;

if (in_array('reporters: report: PhpStorm_Codeception_ReportPrinter', $_SERVER['argv'] ?? [])) {
    ob_start();
}

// ensure we get report on all possible php errors
error_reporting(-1);

define('YII_ENABLE_ERROR_HANDLER', false);
define('YII_DEBUG', true);
define('YII_ENV', 'test');
$_SERVER['SCRIPT_NAME'] = '/' . __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

$projectRoot = dirname(__DIR__);

require_once($projectRoot . '/vendor/autoload.php');
require_once($projectRoot . '/vendor/yiisoft/yii2/Yii.php');

if (is_readable(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/..');
    $dotenv->load();
}

Yii::setAlias('@Yii2Oauth2ServerTests', __DIR__);
Yii::setAlias('@rhertogh/Yii2Oauth2Server', $projectRoot . '/src');

Yii::setAlias('@vendor', $projectRoot . '/vendor');

Json::$prettyPrint = YII_DEBUG;
