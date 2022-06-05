<?php

namespace rhertogh\Yii2Oauth2Server\models\traits;

trait Oauth2EnabledTrait
{
    /**
     * @inheritDoc
     */
    public function isEnabled()
    {
        return (bool)$this->enabled;
    }

    /**
     * @inheritDoc
     */
    public function setEnabled($isEnabled)
    {
        $this->enabled = (int)$isEnabled;
    }
}
