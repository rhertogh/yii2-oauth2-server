<?php

namespace rhertogh\Yii2Oauth2Server\interfaces\models\queries\base;

interface Oauth2EnabledQueryInterface
{
    /**
     * Set 'enabled' where clause.
     * @param bool $enabled
     * @return $this
     * @since 1.0.0
     */
    public function enabled($enabled = true);
}
