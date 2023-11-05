<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\client;

use rhertogh\Yii2Oauth2Server\controllers\console\client\base\Oauth2BaseClientAction;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2ClientController;
use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use yii\helpers\Console;

/**
 * @property Oauth2ClientController $controller
 */
class Oauth2ViewClientAction extends Oauth2BaseClientAction
{
    public function run($id)
    {
        $module = $this->controller->module;
        $client = $this->findByIdOrIdentifier($id);

        $clientInfo = [
            'ID' => $client->getPrimaryKey(),
            'Identifier' => $client->getIdentifier(),
            'Name' => $client->getName(),
            'Type' => $client->isConfidential() ? 'Confidential (client secret required)' : 'Public (no client secret required)',
            'Redirect URIs' => implode(', ', $client->getRedirectUri()),
            'Allow Variable URI Query' => $client->isVariableRedirectUriQueryAllowed() ? 'Yes' : 'No',
            'Grant Types' => implode(',', Oauth2Module::getGrantTypeIdentifiers($client->getGrantTypes())),
            'Allow Generic Scopes' => $client->getAllowGenericScopes() ? 'Yes' : 'No',
            'Throw Exception on invalid Scope' => $client->getExceptionOnInvalidScope() !== null
                ? ($client->getExceptionOnInvalidScope() ? 'Yes' : 'No')
                : '[Not set, using module: ' . ($module->exceptionOnInvalidScope ? 'Yes' : 'No') . ']',
            'End Users may authorize client' => $client->endUsersMayAuthorizeClient() ? 'Yes' : 'No',
            'User Account Selection' => $client->getUserAccountSelection() !== null
                ? Oauth2Module::USER_ACCOUNT_SELECTION_NAMES[$client->getUserAccountSelection()]
                : '[Not set, using module: '
                    . Oauth2Module::USER_ACCOUNT_SELECTION_NAMES[$module->defaultUserAccountSelection]
                    . ']',
            'Is Auth Code without PKCE allowed' => $client->isAuthCodeWithoutPkceAllowed() ? 'Yes' : 'No',
            'Skip authorization if scope is allowed' => $client->skipAuthorizationIfScopeIsAllowed() ? 'Yes' : 'No',
            'Logo URI' => $client->getLogoUri(),
            'Terms of Service URI' => $client->getTermsOfServiceUri(),
            'Contacts' => $client->getContacts(),
        ];

        if ($client->validateGrantType(Oauth2Module::GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS)) {
            $clientInfo['Client Credentials Grant User'] = $client->getClientCredentialsGrantUserId();
        }

        if ($module->enableOpenIdConnect) {
            $clientInfo['OIDC allow offline access without consent'] =
                $client->getOpenIdConnectAllowOfflineAccessWithoutConsent() ? 'Yes' : 'No';
        }

        $clientInfo['Enabled'] = $client->isEnabled() ? 'Yes' : 'No';

        $this->controller->stdout(Table::widget([
            'rows' => array_map(fn($property) => [$property, $clientInfo[$property]], array_keys($clientInfo)),
        ]));

        $scopes = $client->getAllowedScopes(true);

        if ($this->controller->verbose) {
            $this->controller->stdout(PHP_EOL);

            usort(
                $scopes,
                fn(Oauth2ScopeInterface $a, Oauth2ScopeInterface $b) => strcmp($a->getIdentifier(), $b->getIdentifier())
            );

            $scopeInfo = [];
            $showInheritedInfo = false;
            foreach ($scopes as $scope) {
                $clientScope = $scope->getClientScope($client->getPrimaryKey());
                $appliedByDefault = null;
                $requiredOnAuthorization = null;
                if ($clientScope) {
                    if ($clientScope->getAppliedByDefault() !== null) {
                        $appliedByDefault = $this->generateAppliedByDefaultLabel($clientScope->getAppliedByDefault());
                    }
                    if ($clientScope->getRequiredOnAuthorization() !== null) {
                        $requiredOnAuthorization = ($clientScope->getRequiredOnAuthorization() ? 'Yes' : 'No');
                    }
                }
                if (empty($appliedByDefault)) {
                    $appliedByDefault = $this->generateAppliedByDefaultLabel($scope->getAppliedByDefault()) . '¹';
                    $showInheritedInfo = true;
                }
                if (empty($requiredOnAuthorization)) {
                    $requiredOnAuthorization = ($scope->getRequiredOnAuthorization() ? 'Yes' : 'No') . '¹';
                    $showInheritedInfo = true;
                }

                $scopeInfo[] = [
                    $scope->getIdentifier(),
                    $appliedByDefault,
                    $requiredOnAuthorization,
                    $clientScope ? 'Client' : 'Generic',
                ];
            }

            $this->controller->stdout(count($scopes) . ' scope(s) configured for "'
                . $client->getIdentifier() . '" client using '
                . ($client->getAllowGenericScopes()
                    ? 'generic mode (scopes don\'t need to be explicitly configured for this client)'
                    : 'strict mode (scopes need to be explicitly configured for this client)'
                )
                . '.' . PHP_EOL);

            $this->controller->stdout(Table::widget([
                'headers' => [
                    'Scope',
                    'Applied by default',
                    'Required on authorization',
                    'Origin',
                ],
                'rows' => $scopeInfo,
            ]));

            if ($showInheritedInfo) {
                $this->controller->stdout('¹ Config inherited from scope.'. PHP_EOL);
            }

            /** @var Oauth2ClientScopeInterface $clientScopeClass */
            $clientScopeClass = DiHelper::getValidatedClassName(Oauth2ClientScopeInterface::class);
            $clientScopeTableName = $clientScopeClass::tableName();
            $disabledClientScopes = $clientScopeClass::find()
                ->innerJoinWith('scope')
                ->andWhere([$clientScopeTableName . '.client_id' => $client->getPrimaryKey()])
                ->andWhere([$clientScopeTableName . '.enabled' => false])
                ->select('identifier')
                ->column();

            if ($disabledClientScopes) {
                $this->controller->stdout('Note: there is/are ' . count($disabledClientScopes)
                    . ' scope(s) explicitly disabled for this client: ' . implode(', ', $disabledClientScopes)
                    . '.' . PHP_EOL);
            }

        } else {
            $this->controller->stdout(count($scopes) . ' scope(s) configured for "'
                . $client->getIdentifier() . '" client.' . PHP_EOL);

            if ($scopes) {
                $this->controller->stdout('Hint: use `--verbose` to show scope configuration.'
                    . PHP_EOL, Console::ITALIC);
            }
        }

        return ExitCode::OK;
    }

    protected function generateAppliedByDefaultLabel($appliedByDefault)
    {
        switch ($appliedByDefault) {
            case Oauth2ScopeInterface::APPLIED_BY_DEFAULT_NO:
                return 'No';
            case Oauth2ScopeInterface::APPLIED_BY_DEFAULT_CONFIRM:
                return 'Yes (with user confirm)';
            case Oauth2ScopeInterface::APPLIED_BY_DEFAULT_AUTOMATICALLY:
                return 'Yes (w/o user confirm)';
            case Oauth2ScopeInterface::APPLIED_BY_DEFAULT_IF_REQUESTED:
                return 'Yes (upon client request w/o user confirm)';
        }

        throw new \LogicException('Unknown "applied by default" value: ' . $appliedByDefault);
    }
}
