<?php

namespace rhertogh\Yii2Oauth2Server\migrations\import;

use rhertogh\Yii2Oauth2Server\controllers\console\base\traits\GenerateClientsTableTrait;
use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\migrations\base\Oauth2BaseMigration;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Query;
use yii\helpers\Console;

/**
 * phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
abstract class Oauth2_FilshYii2Oauth2ServerImportMigration extends Oauth2BaseMigration
{
    use GenerateClientsTableTrait;

    /**
     * @inheritDoc
     */
    public static function generationIsActive($module)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function safeUp()
    {
        $controller = Yii::$app->controller;
        $module = Oauth2Module::getInstance();
        if (empty($module)) {
            throw new InvalidConfigException('Oauth2Module is not instantiated. Is it added to the config in the "module" and "bootstrap" section?');
        }

        $clientsData = $this->getImportClientData();

        $controller->stdout('Found ' . count($clientsData) . ' client(s) for import.' . PHP_EOL);

        $scopeIdentifiers = [];
        foreach (array_column($clientsData, 'scope') as $scope) {
            if ($scope) {
                $scopeIdentifiers = array_merge($scopeIdentifiers, array_filter(array_map('trim', explode(' ', $scope))));
            }
        }
        $scopeIdentifiers = array_unique($scopeIdentifiers);

        /** @var class-string<Oauth2ScopeInterface> $scopeClass */
        $scopeClass = DiHelper::getValidatedClassName(Oauth2ScopeInterface::class);

        $existingScopeIdentifiers = $scopeClass::find()->select('identifier')->column();

        $missingScopeIdentifiers = array_diff($scopeIdentifiers, $existingScopeIdentifiers);

        foreach ($missingScopeIdentifiers as $scopeIdentifier) {
            $scope = new $scopeClass([
                'identifier' => $scopeIdentifier,
            ]);
            $scope->persist();
        }

        if ($missingScopeIdentifiers) {
            $controller->stdout('Created ' . count($missingScopeIdentifiers) . ' new scope(s): '
                . implode(', ', $missingScopeIdentifiers) . PHP_EOL);
        }

        /** @var class-string<Oauth2ClientInterface> $clientClass */
        $clientClass = DiHelper::getValidatedClassName(Oauth2ClientInterface::class);

        $newClients = [];
        $existingClients = [];
        $warnings = [];
        foreach ($clientsData as $index => $clientData) {
            if (empty($clientData['client_id'])) {
                throw new InvalidConfigException('Empty `client_id` at row ' . ($index + 1));
            }
            $clientIdentifier = $clientData['client_id'];
            $client = $clientClass::findByIdentifier($clientIdentifier);
            if ($client) {
                $existingClients[] = $client;
                continue;
            } else {
                $client = new $clientClass([
                    'identifier' => $clientIdentifier,
                    'name' => $clientIdentifier,
                ]);
            }

            if (empty($clientData['redirect_uri'])) {
                throw new InvalidConfigException('Empty `redirect_uri` for client ' . $clientIdentifier);
            }
            $client->setRedirectUri($clientData['redirect_uri']);

            if (empty($clientData['grant_types'])) {
                throw new InvalidConfigException('Empty `grant_types` for client ' . $clientIdentifier);
            }
            $client->setGrantTypes(array_reduce(
                array_filter(array_map('trim', explode(' ', $clientData['grant_types']))),
                fn ($grantType, $identifier) => $grantType |= Oauth2Module::getGrantTypeId($identifier),
            ));

            if ($clientData['client_secret']) {
                if (mb_strlen($clientData['client_secret']) < $client->getMinimumSecretLength()) {
                    $warnings[] = 'The client secret length for "' . $clientIdentifier . '" is less then the minimum of '
                        . $client->getMinimumSecretLength() . ' characters, you might want to set a stronger secret.';
                    $client->setMinimumSecretLength(1);
                }
                $client
                    ->setType(Oauth2ClientInterface::TYPE_CONFIDENTIAL)
                    ->setSecret($clientData['client_secret'], $module->getCryptographer());
            } else {
                $client->setType(Oauth2ClientInterface::TYPE_PUBLIC);
            }

            $client->setClientCredentialsGrantUserId($clientData['user_id'] ?? null);

            $scopes = isset($clientData['scope'])
                ? array_filter(array_map('trim', explode(' ', $clientData['scope'])))
                : [];

            $client
                ->persist()
                ->syncClientScopes($scopes, $module->getScopeRepository());

            $newClients[] = $client;
        }

        if ($newClients) {
            $controller->stdout('Created ' . count($newClients) . ' new client(s):' . PHP_EOL);
            $controller->stdout($this->generateClientsTable($newClients));
            $controller->stdout(PHP_EOL);
        }

        if ($existingClients) {
            $controller->stdout(count($existingClients) . ' existing '
                . (count($existingClients) > 1 ? 'clients were' : 'client was' ) . ' skipped: '
                . implode(', ', array_column($existingClients, 'identifier')) . PHP_EOL);
        }

        if ($warnings) {
            $controller->stdout('Warnings:' . PHP_EOL, Console::FG_YELLOW);
            foreach ($warnings as $warning) {
                $controller->stdout(' - ' . $warning . PHP_EOL, Console::FG_YELLOW);
            }
            $controller->stdout(PHP_EOL);
        }
    }

    protected function getImportClientData()
    {
        return (new Query())
            ->from($this->getImportClientTable())
            ->all($this->db);
    }

    protected function getImportClientTable()
    {
        $clientTable = $this->config['clientTable'] ?? null;
        if (empty($clientTable)) {
            throw new InvalidConfigException('$config[\'clientTable\'] must be set.');
        }
        return $clientTable;
    }

    /**
     * @inheritDoc
     */
    public function safeDown()
    {
        // Note: Clients and/or Scopes are never removed.
    }
}
