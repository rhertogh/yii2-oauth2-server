<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\client;

use rhertogh\Yii2Oauth2Server\controllers\console\client\base\Oauth2BaseClientAction;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2ClientController;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * @property Oauth2ClientController $controller
 */
class Oauth2DeleteClientAction extends Oauth2BaseClientAction
{
    public function run($id)
    {
        $controller = $this->controller;
        $client = $this->findByIdOrIdentifier($id);

        $clientInfo = implode('-', $client->getPrimaryKey(true)) . ' (' . $client->getIdentifier() . ')';

        if (
            $controller->confirm('Are you sure you want to delete Client ' . $clientInfo
                . '? This action can not be undone!')
        ) {
            $client->delete();
            if ($controller->verbose) {
                $controller->stdout('Deleted Client ' . $clientInfo . PHP_EOL, Console::FG_GREEN);
            }
        }

        return ExitCode::OK;
    }
}
