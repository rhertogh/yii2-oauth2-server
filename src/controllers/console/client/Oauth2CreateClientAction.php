<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\client;

use rhertogh\Yii2Oauth2Server\controllers\console\client\base\Oauth2BaseEditClientAction;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2ClientController;
use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeCollectionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\console\client\Oauth2CreateClientActionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\base\InvalidArgumentException;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * @property Oauth2ClientController $controller
 */
class Oauth2CreateClientAction extends Oauth2BaseEditClientAction implements Oauth2CreateClientActionInterface
{
    public function run()
    {
        $controller = $this->controller;
        $module = $controller->module;

        /** @var class-string<Oauth2ClientInterface> $clientClass */
        $clientClass = DiHelper::getValidatedClassName(Oauth2ClientInterface::class);
        /** @var Oauth2ClientInterface $client */
        $client = new $clientClass();

        if (!empty($controller->sample)) {
            $sample = strtolower($controller->sample);

            if ($sample == 'postman') {
                $postmanIdentifier = 'postman-sample-client';
                $defaultIdentifier = $postmanIdentifier;
                $postmanIdentifierCount = 1;
                while ($clientClass::findByIdentifier($defaultIdentifier)) {
                    $defaultIdentifier = $postmanIdentifier . '-' . ++$postmanIdentifierCount;
                }
                $client->setIdentifier($defaultIdentifier);
                $client->setName('Postman Sample Client');
                $client->setRedirectUri(['https://oauth.pstmn.io/v1/callback']);

                $defaultGrantTypes = 0;
                foreach ($module->getAuthorizationServer()->getEnabledGrantTypes() as $grantType) {
                    $defaultGrantTypes |= Oauth2Module::getGrantTypeId($grantType->getIdentifier());
                }
                $client->setGrantTypes($defaultGrantTypes);

                if ($module->enableOpenIdConnect) {
                    $defaultScopes = implode(' ', Oauth2OidcScopeCollectionInterface::OPENID_CONNECT_DEFAULT_SCOPES);
                }
            } else {
                throw new InvalidArgumentException('Unknown client sample: "' . $sample . '"');
            }
        }

        $this->editClient($client, $defaultScopes ?? '');
        $clientScopes = $client->getClientScopes()->with('scope')->all();
        $scopes = implode(' ', ArrayHelper::getColumn($clientScopes, 'scope.identifier'));

        if ($controller->interactive || $controller->verbose) {
            $controller->stdout('Successfully created new client with id "' . $client->getPrimaryKey()
                . '", identifier "' . $client->getIdentifier()
                . '"' . ($scopes ? (' and scopes "' . $scopes . '"') : '') . '.' . PHP_EOL, Console::FG_GREEN);
        }

        return ExitCode::OK;
    }
}
