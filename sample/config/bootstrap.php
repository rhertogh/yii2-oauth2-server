<?php

use yii\helpers\Json;

$sampleRoot = dirname(__DIR__, 1);
$projectRoot = dirname(__DIR__, 2);

Yii::setAlias('@rhertogh/Yii2Oauth2Server',  $projectRoot . '/src');
Yii::setAlias('@vendor',  $projectRoot . '/vendor');

Yii::setAlias('@sample',  $sampleRoot);

Json::$prettyPrint = YII_DEBUG;
