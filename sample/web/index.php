<?php

use yii\web\Application;

// phpcs:disable PSR1.Files.SideEffects

defined('YII_ENV') or define('YII_ENV', 'dev');
defined('YII_ENV_PROD') or define('YII_ENV_PROD', YII_ENV === 'production');
defined('YII_DEBUG') or define('YII_DEBUG', YII_ENV === 'dev');

require(__DIR__ . '/../../vendor/autoload.php');
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';

require(__DIR__ . '/../config/bootstrap.php');

$config = require(__DIR__ . '/../config/site.php');

$application = new Application($config);
$application->run();
