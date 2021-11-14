<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\client;

use League\OAuth2\Server\Grant\GrantTypeInterface;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2ClientController;
use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeCollectionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientScopeInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\Action;
use yii\base\InvalidArgumentException;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * @property Oauth2ClientController $controller
 */
class Oauth2CreateClientAction extends Action
{
    public function run()
    {
        $module = $this->controller->module;

        $identifier = $this->controller->identifier;
        $name = $this->controller->name;
        $type = $this->controller->type;
        $redirectURIs = $this->controller->redirectURIs;
        $grantTypes = $this->controller->grantTypes;
        $secret = $this->controller->secret;
        $scopes = $this->controller->scopes;

        if (!empty($this->controller->sample)) {

            $sample = strtolower($this->controller->sample);

            if ($sample == 'postman') {
                $defaultIdentifier = 'postman-sample-client';
                $defaultName = 'Postman Sample Client';
                $redirectURIs = $redirectURIs ?? ['https://oauth.pstmn.io/v1/callback'];
                $type = $type ?? Oauth2ClientInterface::TYPE_CONFIDENTIAL;
                if ($module->enableOpenIdConnect) {
                    $defaultScopes = implode(' ', Oauth2OidcScopeCollectionInterface::OPENID_CONNECT_DEFAULT_SCOPES);
                }
            } else {
                throw new InvalidArgumentException('Unknown client sample: "' . $sample . '"');
            }
        }

        $this->controller->stdout("Creating new Oath2 Client\n");

        if (empty($identifier)) {
            $identifier = $this->controller->prompt('Client Identifier?', [
                'required' => true,
                'default' => $defaultIdentifier ?? null,
                'validator' => function ($input, &$error) {
                    /** @var string|Oauth2ClientInterface $clientClass */
                    $clientClass = DiHelper::getValidatedClassName(Oauth2ClientInterface::class);
                    if ($clientClass::findByIdentifier($input)) {
                        $error = 'A client with identifier "' . $input . '" already exists.';
                        return false;
                    }
                    return true;
                },
            ]);
        }

        if (empty($name)) {
            $name = $this->controller->prompt('Client Name?', [
                'required' => true,
                'default' => $defaultName ?? null,
            ]);
        }

        if (empty($type)) {
            $clientTypeOptions = [
                Oauth2ClientInterface::TYPE_CONFIDENTIAL => 'Confidential',
                Oauth2ClientInterface::TYPE_PUBLIC => 'Public',
            ];
            $clientTypeOptionsWithDetails = [
                Oauth2ClientInterface::TYPE_CONFIDENTIAL => 'Confidential: Identifies the client via a shared secret.'
                    . ' Note: This should only be trusted in case the client can store the secret securely'
                    . ' (e.g. another server).',
                Oauth2ClientInterface::TYPE_PUBLIC => 'Public: In case the client can not store a secret securely'
                    . ' it should be declared public (e.g. web- or mobile applications).',
            ];
            $this->controller->stdout("Client Type options:\n");
            foreach ($clientTypeOptions as $key => $value) {
                $this->controller->stdout(" $key - $value\n");
            }
            $type = $this->controller->select('Client Type?', $clientTypeOptionsWithDetails);
        }

        if (empty($redirectURIs)) {
            $redirectURIs = $this->controller->prompt('Client Redirect URIs (comma separated))?', [
                'required' => true,
            ]);
            $redirectURIs = array_map('trim', explode(',', $redirectURIs));
        }

        if (empty($grantTypes)) {
            $availableGrantTypes = array_map(
                fn(GrantTypeInterface $grant) => $grant->getIdentifier(),
                $module->getAuthorizationServer()->getEnabledGrantTypes()
            );

            $grantTypes = 0;
            foreach ($availableGrantTypes as $availableGrantType) {
                if ($this->controller->confirm('Enable "' . $availableGrantType . '" grant?')) {
                    $grantTypes |= Oauth2Module::getGrantTypeId($availableGrantType);
                }
            }
        }

        //ToDo: start transaction when DB specification is supported.

        /** @var Oauth2ClientInterface $client */
        $client = Yii::createObject([
            'class' => Oauth2ClientInterface::class,
            'identifier' => $identifier,
            'type' => $type,
            'name' => $name,
            'redirect_uris' => $redirectURIs,
            'token_types' => 1, # Bearer
            'grant_types' => $grantTypes,
        ]);

        if ($type == Oauth2ClientInterface::TYPE_CONFIDENTIAL) {
            if (!empty($secret) && !$client->validateNewSecret($secret, $error)) {
                $this->controller->stdout("Invalid secret: $error\n");
                $secret = null;
            }
            if (empty($secret)) {
                $secret = $this->controller->prompt('Client Secret?', [
                    'required' => true,
                    'validator' => [$client, 'validateNewSecret'],
                ]);
            }

            $client->setSecret($secret, $module->getEncryptor());
        }


        $client->persist();

        if (!empty($scopes) && !$this->validateScope($scopes, $error)) {
            $this->controller->stdout("Invalid scopes: $error\n");
            $scopes = null;
        }
        if (empty($scopes)) {
            $scopes = $this->controller->prompt('Client Scopes?', [
                'required' => false,
                'default' => $defaultScopes ?? null,
                'validator' => [$this, 'validateScope'],
            ]);
        }

        if (!empty($scopes)) {
            $scopeIdentifiers = explode(' ', $scopes);
            foreach ($scopeIdentifiers as $scopeIdentifier) {
                /** @var Oauth2ClientScopeInterface $clientScope */
                $clientScope = Yii::createObject([
                    'class' => Oauth2ClientScopeInterface::class,
                    'client_id' => $client->getPrimaryKey(),
                    'scope_id' => $module->getScopeRepository()
                        ->findModelByIdentifier($scopeIdentifier)->getPrimaryKey(),
                ]);
                $clientScope->persist();
            }
        }

        $this->controller->stdout('Successfully created new client with identifier "' . $client->getIdentifier() .
            '"' . ($scopes ? (' and scopes "' . $scopes . '"') : '') . ".\n", Console::FG_GREEN);

        return ExitCode::OK;
    }

    public function validateScope($scope, &$error)
    {
        $error = null;
        if (!empty($scope)) {
            $scopeRepository = $this->controller->module->getScopeRepository();
            $scopeIdentifiers = explode(' ', $scope);
            $unknownScopeIdentifiers = [];
            foreach ($scopeIdentifiers as $scopeIdentifier) {
                if (empty($scopeRepository->getScopeEntityByIdentifier($scopeIdentifier))) {
                    $unknownScopeIdentifiers[] = $scopeIdentifier;
                }
            }
            if ($unknownScopeIdentifiers) {
                $error = 'Unknown identifiers: ' . implode(', ', $unknownScopeIdentifiers);
            }
        }

        return $error === null;
    }
}
