<?php

require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// The sample app config requires the `YII_DB_DRIVER` environment variable,
// which is not set when running outside Docker.
if (getenv('YII_DB_DRIVER') === false) {
    putenv('YII_DB_DRIVER=MySQL');
}
