<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\client\base;

use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2ClientController;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\console\Exception;

/**
 * @property Oauth2ClientController $controller
 */
class Oauth2BaseClientAction extends Action
{
    /**
     * Find a client by its id or identifier.
     *
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function findByIdOrIdentifier($id)
    {
        $module = $this->controller->module;
        $client = $module->getClientRepository()->findModelByPkOrIdentifier($id);

        if (empty($client)) {
            throw new Exception('No client with id or identifier "' . $id . '" found.' . PHP_EOL);
        }

        return $client;
    }
}
