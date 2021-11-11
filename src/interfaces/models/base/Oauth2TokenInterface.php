<?php


namespace rhertogh\Yii2Oauth2Server\interfaces\models\base;


interface Oauth2TokenInterface extends Oauth2ActiveRecordInterface
{
    /**
     * Set the model's revocation status.
     * @param bool $isRevoked
     * @return void
     * @since 1.0.0
     */
    public function setRevokedStatus($isRevoked);

    /**
     * Get the model's revocation status
     * @return bool
     * @since 1.0.0
     */
    public function getRevokedStatus();
}
