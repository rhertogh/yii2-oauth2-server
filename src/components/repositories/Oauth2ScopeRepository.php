<?php

namespace rhertogh\Yii2Oauth2Server\components\repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use rhertogh\Yii2Oauth2Server\components\repositories\base\Oauth2BaseRepository;
use rhertogh\Yii2Oauth2Server\components\repositories\traits\Oauth2RepositoryIdentifierTrait;
use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserClientScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2ScopeRepositoryInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\models\Oauth2Scope;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;

class Oauth2ScopeRepository extends Oauth2BaseRepository implements Oauth2ScopeRepositoryInterface
{
    use Oauth2RepositoryIdentifierTrait;

    /**
     * @inheritDoc
     * @return Oauth2ScopeInterface|string
     */
    public function getModelClass()
    {
        return Oauth2ScopeInterface::class;
    }

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        return $this->findModelByIdentifier($identifier);
    }

    /**
     * @inheritDoc
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        if (!($clientEntity instanceof Oauth2ClientInterface)) {
            throw new InvalidArgumentException(
                get_class($clientEntity) . ' must implement ' . Oauth2ClientInterface::class
            );
        } else {
            /** @var Oauth2ClientInterface $client */
            $client = $clientEntity;
        }

        $requestedScopeIdentifiers = array_map(fn(Oauth2ScopeInterface $scope) => $scope->getIdentifier(), $scopes);

        $clientAllowedScopes = $client->getAllowedScopes($requestedScopeIdentifiers);

        if (empty($userIdentifier)) {
            // Only allow scopes without user if grant type is 'client_credentials'
            if ($grantType !== Oauth2Module::GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS) {
                throw new InvalidArgumentException(
                    '$userIdentifier is required when $grantType is not "client_credentials".'
                );
            }

            return $clientAllowedScopes;
        }

        $scopeIds = array_map(fn($scope) => $scope->getPrimaryKey(), $clientAllowedScopes);

        /** @var Oauth2ScopeInterface $scopeClass */
        $scopeClass = DiHelper::getValidatedClassName(Oauth2ScopeInterface::class);

        $approvedScopes = $scopeClass::find()
            ->alias('scope')
            ->innerJoinWith('userClientScopes user_client_scope', false)
            ->andWhere([
                'scope.id' => $scopeIds,
                'user_client_scope.user_id' => $userIdentifier,
                'user_client_scope.client_id' => $client->getPrimaryKey(),
                'user_client_scope.enabled' => 1,
            ])
            ->indexBy('id')
            ->all();

        foreach ($clientAllowedScopes as $clientAllowedScope) {
            $clientScope = $clientAllowedScope->getClientScope($client->getPrimaryKey());
            $appliedByDefault = ($clientScope ? $clientScope->getAppliedByDefault() : null)
                ?? $clientAllowedScope->getAppliedByDefault();
            $scopeId = $clientAllowedScope->getPrimaryKey();
            if (
                $appliedByDefault === Oauth2Scope::APPLIED_BY_DEFAULT_AUTOMATICALLY
                && !array_key_exists($scopeId, $approvedScopes)
            ) {
                $approvedScopes[$scopeId] = $clientAllowedScope;
            }
        }

        return array_values($approvedScopes);
    }
}
