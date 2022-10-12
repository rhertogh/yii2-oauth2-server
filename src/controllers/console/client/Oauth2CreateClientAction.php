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
        $grantTypes = $this->controller->grantTypes;
        $redirectURIs = $this->controller->redirectURIs;
        $secret = $this->controller->secret;
        $scopes = $this->controller->scopes;
        $endUsersMayAuthorizeClient = $this->controller->endUsersMayAuthorizeClient;

        if (!empty($this->controller->sample)) {
            $sample = strtolower($this->controller->sample);

            if ($sample == 'postman') {
                $defaultIdentifier = 'postman-sample-client';
                $defaultName = 'Postman Sample Client';
                $redirectURIs = $redirectURIs ?? ['https://oauth.pstmn.io/v1/callback'];
                $type = $type ?? Oauth2ClientInterface::TYPE_CONFIDENTIAL;

                $defaultGrantTypes = array_fill_keys(
                    array_keys($module->getAuthorizationServer()->getEnabledGrantTypes()),
                    true
                );

                if ($module->enableOpenIdConnect && empty($scopes)) {
                    $scopes = implode(' ', Oauth2OidcScopeCollectionInterface::OPENID_CONNECT_DEFAULT_SCOPES);
                }

            } else {
                throw new InvalidArgumentException('Unknown client sample: "' . $sample . '"');
            }
        }

        $this->controller->stdout('Creating new Oath2 Client' . PHP_EOL);

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
            $this->controller->stdout('Client Type options:' . PHP_EOL);
            foreach ($clientTypeOptions as $key => $value) {
                $this->controller->stdout(" $key - $value" . PHP_EOL);
            }
            $type = $this->controller->select('Client Type?', $clientTypeOptionsWithDetails);
        }

        if (empty($grantTypes)) {
            $availableGrantTypes = array_map(
                fn(GrantTypeInterface $grant) => $grant->getIdentifier(),
                $module->getAuthorizationServer()->getEnabledGrantTypes()
            );

            $grantTypes = 0;
            foreach ($availableGrantTypes as $availableGrantType) {
                if ($this->controller->confirm(
                    'Enable "' . $availableGrantType . '" grant?',
                        $defaultGrantTypes[$availableGrantType] ?? false
                )) {
                    $grantTypes |= Oauth2Module::getGrantTypeId($availableGrantType);
                }
            }
        }

        if (empty($redirectURIs)) {
            $redirectURIs = $this->controller->prompt('Client Redirect URIs (comma separated)?', [
                'required' => true, // ToDo: determine if required based on grant type
            ]);
            $redirectURIs = array_map('trim', explode(',', $redirectURIs));
        }

        $inputClient = Yii::createObject(Oauth2ClientInterface::class);

        if ($type == Oauth2ClientInterface::TYPE_CONFIDENTIAL) {
            if (!empty($secret) && !$inputClient->validateNewSecret($secret, $error)) {
                $this->controller->stdout("Invalid secret: $error" . PHP_EOL);
                $secret = null;
            }
            if (empty($secret)) {
                $secret = $this->controller->prompt('Client Secret?', [
                    'required' => true,
                    'validator' => [$inputClient, 'validateNewSecret'],
                ]);
            }
        }

        if (!empty($scopes) && !$this->validateScope($scopes, $error)) {
            $this->controller->stdout("Invalid scopes: $error" . PHP_EOL);
            $scopes = null;
        }

        if (empty($endUsersMayAuthorizeClient)) {
            $endUsersMayAuthorizeClient = $this->controller->confirm('May end-users authorize this client?', true);
        }

        $client = $module->createClient(
            $identifier,
            $name,
            $grantTypes,
            $redirectURIs,
            $type,
            $secret,
            $scopes,
            null,
            $endUsersMayAuthorizeClient
        );

        $this->controller->stdout('Successfully created new client with identifier "' . $client->getIdentifier() .
            '"' . ($scopes ? (' and scopes "' . $scopes . '"') : '') . '.' . PHP_EOL, Console::FG_GREEN);

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
