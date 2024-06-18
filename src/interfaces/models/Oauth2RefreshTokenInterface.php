<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2TokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AccessTokenQueryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2RefreshTokenQueryInterface;

interface Oauth2RefreshTokenInterface extends
    Oauth2ActiveRecordInterface,
    Oauth2TokenInterface,
    RefreshTokenEntityInterface
{
    /**
     * @inheritDoc
     * @return Oauth2RefreshTokenQueryInterface
     * @since 1.0.0
     */
    public static function find();

    /**
     * Find all models by user identifier
     *
     * @param int[] $accessTokenIds
     * @return static[]|Oauth2RefreshTokenInterface
     * @since 1.0.0
     */
    public static function findAllByAccessTokenIds($accessTokenIds);

    /**
     * Get the access token relation
     * @return Oauth2AccessTokenQueryInterface
     * @since 1.0.0
     */
    public function getAccessTokenRelation();

    # region Oauth2TokenInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2AccessTokenInterface
     */
    public function getAccessToken();
    # endregion
}
