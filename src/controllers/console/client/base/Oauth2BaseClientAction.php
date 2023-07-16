<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\client\base;

use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use yii\base\Action;
use yii\console\Exception;

class Oauth2BaseClientAction extends Action
{
    protected function findByIdOrIdentifier($id)
    {
        $module = $this->controller->module;

        try {
            /** @var Oauth2Client $client */
            $client = $module->getClientRepository()->findModelByPk($id);
        } catch (\Exception $e) {
            // Silently ignore.
        }

        if (empty($client)) {
            // try to find by `identifier`.
            $client = $module->getClientRepository()->findModelByIdentifier($id);
        }

        if (empty($client)) {
            throw new Exception('No client with id or identifier "' . $id . '" found.' . PHP_EOL);
        }

        return $client;
    }
}
