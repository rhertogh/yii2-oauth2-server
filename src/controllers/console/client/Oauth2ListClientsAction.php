<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\client;

use League\OAuth2\Server\Grant\GrantTypeInterface;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2ClientController;
use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeCollectionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\Action;
use yii\base\InvalidArgumentException;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use yii\helpers\Console;

/**
 * @property Oauth2ClientController $controller
 */
class Oauth2ListClientsAction extends Action
{
    public function run()
    {
        $module = $this->controller->module;

        $clients = $module->getClientRepository()->getAllClients();

        if (!$clients) {
            $this->controller->stdout('No clients defined. Run `yii ' . $this->controller->uniqueId . '/create` to define one.' . PHP_EOL);
            return ExitCode::OK;
        }

        $rows = [];
        foreach ($clients as $client) {
            $redirectUris = $client->getRedirectUri();
            $rows[] = [
                'id' => $client->getPrimaryKey(),
                'identifier' => $client->getIdentifier(),
                'type' => $client->isConfidential() ? 'Confidential' : 'Public',
                'redirect_uris' => $redirectUris
                    ? ($redirectUris[0]
                        . (count($redirectUris) > 1
                            ? ' +' . (count($redirectUris) - 1) . ' more'
                            : '')
                    )
                    : '',
                'grant_types' => implode(', ', Oauth2Module::getGrantTypeIdentifiers($client->getGrantTypes())),
            ];
        }

        $this->controller->stdout(Table::widget([
            'headers' => [
                'ID',
                'Identifier',
                'Type',
                'Redirect URIs',
                'Grant Types',
            ],
            'rows' => $rows,
        ]));

        return ExitCode::OK;
    }
}
