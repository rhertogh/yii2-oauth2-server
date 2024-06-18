<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\client;

use rhertogh\Yii2Oauth2Server\controllers\console\client\base\Oauth2BaseEditClientAction;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2ClientController;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\console\client\Oauth2UpdateClientActionInterface;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * @property Oauth2ClientController $controller
 */
class Oauth2UpdateClientAction extends Oauth2BaseEditClientAction implements Oauth2UpdateClientActionInterface
{
    public function run($id)
    {
        $controller = $this->controller;
        $client = $this->findByIdOrIdentifier($id);

        $this->editClient($client);
        $clientScopes = $client->getClientScopes()->with('scope')->all();
        $scopes = implode(' ', ArrayHelper::getColumn($clientScopes, 'scope.identifier'));

        if ($controller->interactive || $controller->verbose) {
            $controller->stdout('Successfully updated client with id "' . $client->getPrimaryKey()
                . '", identifier "' . $client->getIdentifier()
                . '"' . ($scopes ? (' and scopes "' . $scopes . '"') : '') . '.' . PHP_EOL, Console::FG_GREEN);
        }

        return ExitCode::OK;
    }
}
