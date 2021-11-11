<?php


namespace rhertogh\Yii2Oauth2Server\interfaces\models\base;


interface Oauth2IdentifierInterface
{
    /**
     * Find model by identifier. Note: this might be different from the model's id.
     * @param string $identifier
     * @return static|Oauth2ActiveRecordInterface|null
     * @since 1.0.0
     */
    public static function findByIdentifier($identifier);

    /**
     * Get the model identifier. Note: this might be different from the model's id.
     * @return string
     * @since 1.0.0
     */
    public function getIdentifier();

    /**
     * Set the model identifier.
     * @param string $identifier
     * @since 1.0.0
     */
    public function setIdentifier($identifier);

    /**
     * Check if there already is an existing model with the same identifier.
     * @return bool
     * @since 1.0.0
     */
    public function identifierExists();
}
