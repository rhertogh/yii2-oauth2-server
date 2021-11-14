<?php

namespace rhertogh\Yii2Oauth2Server\components\repositories\base;

use rhertogh\Yii2Oauth2Server\exceptions\Oauth2UniqueTokenIdentifierConstraintViolationException;
use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ActiveRecordInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2IdentifierInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2ScopeRelationInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\base\Oauth2TokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\base\Oauth2RepositoryInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

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
