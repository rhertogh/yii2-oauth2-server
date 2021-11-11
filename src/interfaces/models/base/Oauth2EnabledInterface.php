<?php


namespace rhertogh\Yii2Oauth2Server\interfaces\models\base;


interface Oauth2EnabledInterface extends Oauth2ActiveRecordInterface
{
    /**
     * Returns if the model is enabled
     * @return bool
     * @since 1.0.0
     */
    public function isEnabled();

    /**
     * Set the enabled status of the model
     * @param bool $isEnabled
     * @since 1.0.0
     */
    public function setEnabled($isEnabled);
}
