<?php

namespace rhertogh\Yii2Oauth2Server\components\repositories\base;

use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2RepositoryInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\base\Component;

abstract class Oauth2BaseRepository extends Component implements Oauth2RepositoryInterface
{
    /**
     * @var Oauth2Module|null
     */
    protected $_module = null;

    /**
     * @inheritDoc
     */
    public function setModule($module)
    {
        $this->_module = $module;
        return $this;
    }
}
