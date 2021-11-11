<?php


namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries;


use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\base\Oauth2EnabledQueryInterface;
use yii\db\ActiveQueryInterface;

interface Oauth2ClientScopeQueryInterface extends
    Oauth2EnabledQueryInterface,
    ActiveQueryInterface
{
    /**
     * @inheritDoc
     * @return Oauth2ClientScopeInterface[]
     */
    public function all($db = null);
}
