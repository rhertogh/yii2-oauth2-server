<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\client;

use rhertogh\Yii2Oauth2Server\controllers\console\base\traits\GenerateClientsTableTrait;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2ClientController;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\console\client\Oauth2ListClientsActionInterface;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\console\ExitCode;

/**
 * @property Oauth2ClientController $controller
 */
class Oauth2ListClientsAction extends Action implements Oauth2ListClientsActionInterface
{
    use GenerateClientsTableTrait;

    /**
     * List all configured Oauth2 Clients.
     *
     * @return int
     * @throws InvalidConfigException
     */
    public function run()
    {
        $module = $this->controller->module;

        $clients = $module->getClientRepository()->getAllClients();

        if (!$clients) {
            $this->controller->stdout('No clients defined. Run `yii '
                . $this->controller->uniqueId . '/create` to define one.' . PHP_EOL);
            return ExitCode::OK;
        }

        $this->controller->stdout($this->generateClientsTable($clients));

        return ExitCode::OK;
    }
}
