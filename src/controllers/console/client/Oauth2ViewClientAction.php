<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\client;

use rhertogh\Yii2Oauth2Server\controllers\console\client\base\Oauth2BaseClientAction;
use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2ClientController;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\console\ExitCode;
use yii\console\widgets\Table;

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
            'Type' => $client->isConfidential() ? 'Confidential' : 'Public',
            'Redirect URIs' => implode(', ', $client->getRedirectUri()),
            'Allow Variable URI Query' => $client->isVariableRedirectUriQueryAllowed() ? 'Yes' : 'No',
            'Grant Types' => implode(',', Oauth2Module::getGrantTypeIdentifiers($client->getGrantTypes())),
            'Scope Access' => Oauth2ClientInterface::SCOPE_ACCESSES_LABELS[$client->getScopeAccess()],
            'End Users may authorize client' => $client->endUsersMayAuthorizeClient() ? 'Yes' : 'No',
            'User Account Selection' => $client->getUserAccountSelection()
                ? Oauth2Module::USER_ACCOUNT_SELECTION_NAMES[$client->getUserAccountSelection()]
                : '[Using default: '
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

        return ExitCode::OK;
    }
}
