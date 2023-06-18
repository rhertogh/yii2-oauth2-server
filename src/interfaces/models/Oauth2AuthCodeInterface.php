<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2IdentifierInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ScopeRelationInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2TokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AuthCodeQueryInterface;

interface Oauth2AuthCodeInterface extends
    Oauth2ActiveRecordInterface,
    Oauth2IdentifierInterface,
    Oauth2TokenInterface,
    Oauth2ScopeRelationInterface,
    AuthCodeEntityInterface
{
    /**
     * @inheritDoc
     * @return Oauth2AuthCodeQueryInterface
     */
    public static function find();


    # region TokenInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2ClientInterface
     */
    public function getClient();
    # endregion
}
