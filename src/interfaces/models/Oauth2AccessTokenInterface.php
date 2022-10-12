<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordIdInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2IdentifierInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ScopeRelationInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2TokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\queries\Oauth2AccessTokenQueryInterface;
use yii\base\InvalidArgumentException;

interface Oauth2AccessTokenInterface extends
    Oauth2ActiveRecordIdInterface,
    Oauth2IdentifierInterface,
    Oauth2TokenInterface,
    Oauth2ScopeRelationInterface,
    AccessTokenEntityInterface
{
    /**
     * @inheritDoc
     * @return Oauth2AccessTokenQueryInterface
     */
    public static function find();

    /**
     * Performs the data validation.
     * @param string[]|string $attributeNames attribute name or list of attribute names that should be validated.
     * @param bool $clearErrors whether to call [[clearErrors()]] before performing validation
     * @return bool whether the validation is successful without any error.
     * @throws InvalidArgumentException if the current scenario is unknown.
     * @since 1.0.0
     */
    public function validate($attributeNames = null, $clearErrors = true);

    # region TokenInterface methods (overwritten for type covariance)
    /**
     * @inheritDoc
     * @return Oauth2ClientInterface
     */
    public function getClient();

    /**
     * @inheritDoc
     * @return Oauth2ScopeInterface
     */
    public function getScopes();
    # endregion
}
