<?php

namespace rhertogh\Yii2Oauth2Server\traits\models;

use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;

trait Oauth2UserPatTrait
{
    public function generatePersonalAccessToken($clientIdentifier, $scope = null, $clientSecret = true)
    {
        if (property_exists(static::class, 'oauth2ModuleName')) {
            $oauth2ModuleName = static::$oauth2ModuleName;
        }

        /** @var Oauth2Module $module */
        $module = empty($oauth2ModuleName)
            ? Oauth2Module::getInstance()
            : Yii::$app->getModule($oauth2ModuleName);

        return $module->generatePersonalAccessToken(
            $clientIdentifier,
            $this->id,
            $scope,
            $clientSecret
        );
    }
}
