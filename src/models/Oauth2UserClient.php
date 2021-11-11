<?php


namespace rhertogh\Yii2Oauth2Server\models;


use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserClientScopeInterface;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2EnabledTrait;

class Oauth2UserClient extends base\Oauth2UserClient implements Oauth2UserClientInterface
{
    use Oauth2EnabledTrait;
}
