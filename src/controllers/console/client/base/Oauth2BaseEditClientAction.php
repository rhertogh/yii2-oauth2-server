<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\client\base;

use League\OAuth2\Server\Grant\GrantTypeInterface;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2ClientController;
use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\helpers\ArrayHelper;

/**
 * @property Oauth2ClientController $controller
 */
class Oauth2BaseEditClientAction extends Oauth2BaseClientAction
{
    /**
     * @param Oauth2ClientInterface $client
     * @param string $defaultScopes
     * @return void
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function editClient($client, $defaultScopes = '')
    {
        $controller = $this->controller;
        $module = $controller->module;

        $this->configureClient($client);

        $scopes = $this->getScopes($client, $defaultScopes);

        $transaction = $client::getDb()->beginTransaction();
        try {
            $client
                ->persist()
                ->syncClientScopes($scopes, $module->getScopeRepository());
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param Oauth2ClientInterface $client
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    protected function configureClient($client)
    {
        $controller = $this->controller;
        $module = $controller->module;

        $identifier = $controller->identifier;
        if (
            empty($identifier)
            || !$this->clientIdentifierValidator($controller->identifier, $clientIdentifierValidatorError)
        ) {
            if (!empty($clientIdentifierValidatorError)) {
                $controller->stdout($clientIdentifierValidatorError . PHP_EOL);
            }
            $identifier = $controller->prompt('Client Identifier?', [
                'required' => true,
                'default' => $client->getIdentifier(),
                'validator' => [$this, 'clientIdentifierValidator'],
            ]);
        }
        $client->setIdentifier($identifier);

        $name = $controller->name;
        if (empty($name)) {
            $name = $controller->prompt('Client Name?', [
                'required' => true,
                'default' => $client->getName(),
            ]);
        }
        $client->setName($name);

        $type = $controller->type;
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
            if ($controller->interactive) {
                $controller->stdout('Client Type options:' . PHP_EOL);
                foreach ($clientTypeOptions as $key => $value) {
                    $controller->stdout(" $key - $value" . PHP_EOL);
                }
            }
            $type = $controller->select(
                'Client Type?',
                $clientTypeOptionsWithDetails,
                $client->isConfidential()
                    ? Oauth2ClientInterface::TYPE_CONFIDENTIAL
                    : Oauth2ClientInterface::TYPE_PUBLIC
            );
        }
        $type = (int)$type;
        $client->setType($type);

        $grantTypes = $controller->grantTypes;
        if (empty($grantTypes)) {
            $availableGrantTypes = array_map(
                fn(GrantTypeInterface $grant) => $grant->getIdentifier(),
                $module->getAuthorizationServer()->getEnabledGrantTypes()
            );

            if ($controller->interactive) {
                $controller->stdout('Enable Grant Types:' . PHP_EOL);
            }
            $grantTypes = 0;
            foreach ($availableGrantTypes as $availableGrantType) {
                if (
                    $controller->confirm(
                        ' - ' . $availableGrantType,
                        (bool)($client->getGrantTypes() & Oauth2Module::getGrantTypeId($availableGrantType))
                    )
                ) {
                    $grantTypes |= Oauth2Module::getGrantTypeId($availableGrantType);
                }
            }
        }
        $client->setGrantTypes($grantTypes);

        $redirectUrisRequired =
            (bool)($client->getGrantTypes() & (Oauth2Module::GRANT_TYPE_AUTH_CODE | Oauth2Module::GRANT_TYPE_IMPLICIT));

        $redirectURIs = $controller->redirectURIs;
        if (
            empty($controller->redirectURIs)
            && (
                $redirectUrisRequired
                || (
                    !$client->getIsNewRecord() && $client->getRedirectUri()
                )
            )
        ) {
            $redirectURIs = $controller->prompt('Client Redirect URIs (comma separated)?', [
                'required' => $redirectUrisRequired,
                'default' => implode(',', $client->getRedirectUri())
            ]);
            $redirectURIs = array_map('trim', explode(',', $redirectURIs));
        }
        $client->setRedirectUri($redirectURIs);

        if ($type == Oauth2ClientInterface::TYPE_CONFIDENTIAL && $client->getIsNewRecord()) {
            $secret = $controller->secret;
            if (!empty($secret) && !$client->validateNewSecret($secret, $error)) {
                $controller->stdout("Invalid secret: $error" . PHP_EOL);
                $secret = null;
            }
            if (empty($secret)) {
                $secret = $controller->prompt('Client Secret?', [
                    'required' => true,
                    'validator' => [$client, 'validateNewSecret'],
                ]);
            }
            $client->setSecret($secret, $module->getCryptographer());
        }

        if ($controller->allowVariableRedirectUriQuery !== null) {
            $client->setAllowVariableRedirectUriQuery((bool)$controller->allowVariableRedirectUriQuery);
        }
        if ($controller->scopeAccess !== null) {
            $client->setScopeAccess((int)$controller->scopeAccess);
        }
        if ($controller->endUsersMayAuthorizeClient !== null) {
            $client->setEndUsersMayAuthorizeClient((bool)$controller->endUsersMayAuthorizeClient);
        }
        if ($controller->userAccountSelection !== null) {
            $client->setUserAccountSelection((int)$controller->userAccountSelection);
        }
        if ($controller->isAuthCodeWithoutPkceAllowed !== null) {
            $client->setAllowAuthCodeWithoutPkce((bool)$controller->isAuthCodeWithoutPkceAllowed);
        }
        if ($controller->skipAuthorizationIfScopeIsAllowed !== null) {
            $client->setSkipAuthorizationIfScopeIsAllowed((bool)$controller->skipAuthorizationIfScopeIsAllowed);
        }
        if ($controller->logoUri !== null) {
            $client->setLogoUri($controller->logoUri);
        }
        if ($controller->termsOfServiceUri !== null) {
            $client->setTermsOfServiceUri($controller->termsOfServiceUri);
        }
        if ($controller->contacts !== null) {
            $client->setContacts($controller->contacts);
        }
    }

    /**
     * @param Oauth2ClientInterface $client
     * @return string
     */
    protected function getScopes($client, $defaultScopes = '')
    {
        $controller = $this->controller;
        $scopes = $controller->scopes;
        if (!empty($scopes) && !$this->validateScope($scopes, $error)) {
            $controller->stdout("Invalid scopes: $error" . PHP_EOL);
            $scopes = null;
        }
        if (empty($scopes)) {
            if (!$client->getIsNewRecord()) {
                $clientScopes = $client->getClientScopes()->with('scope')->all();
                $defaultScopes = implode(' ', ArrayHelper::getColumn($clientScopes, 'scope.identifier'));
            }
            $scopes = $controller->prompt('Scopes (space separated)?', [
                'default' => $defaultScopes
            ]);
            $scopes = implode(' ', array_filter(array_map('trim', explode(' ', $scopes))));
        }

        return $scopes;
    }

    public function validateScope($scope, &$error)
    {
        $error = null;
        if (!empty($scope)) {
            $scopeRepository = $this->controller->module->getScopeRepository();
            $scopeIdentifiers = array_map('trim', explode(' ', $scope));
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

    public function clientIdentifierValidator($input, &$error)
    {
        /** @var string|Oauth2ClientInterface $clientClass */
        $clientClass = DiHelper::getValidatedClassName(Oauth2ClientInterface::class);
        if ($clientClass::findByIdentifier($input)) {
            $error = 'A client with identifier "' . $input . '" already exists.';
            return false;
        }
        return true;
    }
}
