<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use yii\db\ActiveQueryInterface;

interface Oauth2ClientQueryInterface extends ActiveQueryInterface
{
    /**
     * @inheritDoc
     * @return Oauth2ClientInterface[]
     */
    public function all($db = null);
}
