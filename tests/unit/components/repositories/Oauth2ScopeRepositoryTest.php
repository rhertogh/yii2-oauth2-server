<?php

namespace Yii2Oauth2ServerTests\unit\components\repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2ClientRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2ScopeRepositoryInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\models\Oauth2Scope;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use Yii2Oauth2ServerTests\unit\components\repositories\_base\BaseOauth2RepositoryTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\repositories\Oauth2ScopeRepository
 *
 * @method Oauth2ClientInterface|string getModelClass()
 */
class Oauth2ScopeRepositoryTest extends BaseOauth2RepositoryTest
{
    /**
     * @return Oauth2ScopeInterface|string
     */
    protected function getModelInterface()
    {
        return Oauth2ScopeInterface::class;
    }

    /**
     * @param string $identifier
     * @param string|null $identifier
     *
     * @dataProvider getScopeEntityByIdentifierProvider
     */
    public function testGetScopeEntityByIdentifier($identifier, $class)
    {
        $client = $this->getScopeRepository()->getScopeEntityByIdentifier($identifier);

        if ($class !== null) {
            $this->assertInstanceOf($class, $client);
        } else {
            $this->assertNull($client);
        }
    }

    /**
     * @return array[]
     * @see testGetScopeEntityByIdentifier()
     */
    public function getScopeEntityByIdentifierProvider()
    {
        return [
            ['user.username.read', $this->getModelInterface()],
            ['does-not-exist', null],
        ];
    }

    /**
     * @dataProvider finalizeScopesProvider
     */
    public function testFinalizeScopes(
        $clientId,
        $userId,
        $requestedScopeIdentifiers,
        $scopeAccess,
        $grantType,
        $expectedScopes
    ) {
        $scopeRepository = $this->getScopeRepository();

        $client = Oauth2Client::findOne($clientId);
        $client->scope_access = $scopeAccess;
        $requestedScopes = Oauth2Scope::findAll(['identifier' => $requestedScopeIdentifiers]);

        $finalizedScopes = array_column(
            $scopeRepository->finalizeScopes(
                $requestedScopes,
                $grantType,
                $client,
                $userId
            ),
            'identifier'
        );

        $this->assertEquals($expectedScopes, $finalizedScopes);
    }

    public function finalizeScopesProvider()
    {
        return [
            [
                1003000,
                123,
                [],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_AUTH_CODE,
                [
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                ],
            ],
            [
                1003000,
                123,
                [],
                Oauth2Client::SCOPE_ACCESS_PERMISSIVE,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_AUTH_CODE,
                [
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                    'applied-automatically-by-default-not-assigned',
                    'applied-automatically-by-default-not-assigned-not-required',
                ],
            ],
            [
                1003000,
                null,
                [
                    'user.username.read',
                    'user.email_address.read',
                    'defined-but-not-assigned',
                    'disabled-scope',
                    'disabled-scope-for-client',
                    'non-existing',
                ],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS,
                [
                    'user.id.read',
                    'user.username.read',
                    'user.email_address.read',
                    'applied-by-default',
                    'applied-by-default-for-client',
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                    'applied-by-default-by-client-not-required-for-client',
                ],
            ],
            [
                1003000,
                null,
                [
                    'user.username.read',
                    'user.email_address.read',
                    'defined-but-not-assigned',
                    'disabled-scope',
                    'disabled-scope-for-client',
                    'non-existing',
                ],
                Oauth2Client::SCOPE_ACCESS_PERMISSIVE,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS,
                [
                    'user.id.read',
                    'user.username.read',
                    'user.email_address.read',
                    'applied-by-default',
                    'applied-by-default-for-client',
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                    'applied-by-default-by-client-not-required-for-client',
                    'defined-but-not-assigned',
                    'applied-automatically-by-default-not-assigned',
                    'applied-automatically-by-default-not-assigned-not-required',
                ],
            ],
            [
                1003002,
                124,
                [
                    'user.username.read',
                    'user.email_address.read',
                    'defined-but-not-assigned',
                    'disabled-scope',
                    'disabled-scope-for-client',
                    'non-existing',
                ],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_AUTH_CODE,
                [
                    'user.id.read',
                    'user.username.read',
                    'user.email_address.read',
                    'applied-automatically-by-default-for-client',
                ],
            ],
            [
                1003002,
                124,
                [
                    'user.username.read',
                    'user.email_address.read',
                    'defined-but-not-assigned',
                    'disabled-scope',
                    'disabled-scope-for-client',
                    'non-existing',
                ],
                Oauth2Client::SCOPE_ACCESS_PERMISSIVE,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_AUTH_CODE,
                [
                    'user.id.read',
                    'user.username.read',
                    'user.email_address.read',
                    'applied-automatically-by-default-for-client',
                    'applied-automatically-by-default-not-assigned',
                    'applied-automatically-by-default-not-assigned-not-required',
                ],
            ],
        ];
    }

    public function testFinalizeScopesInvalidClient()
    {
        $scopeRepository = $this->getScopeRepository();

        $client = new class implements ClientEntityInterface {
            public function getIdentifier()
            {
            }
            public function getName()
            {
            }
            public function getRedirectUri()
            {
            }
            public function isConfidential()
            {
            }
        };

        $this->expectExceptionMessage(get_class($client) . ' must implement ' . Oauth2ClientInterface::class);
        $scopeRepository->finalizeScopes(
            [],
            Oauth2Module::GRANT_TYPE_IDENTIFIER_AUTH_CODE,
            $client,
            123
        );
    }

    public function testFinalizeScopesInvalidUserId()
    {
        $scopeRepository = $this->getScopeRepository();

        $client = Oauth2Client::findOne(1003000);

        $this->expectExceptionMessage('$userIdentifier is required when $grantType is not "client_credentials"');
        $scopeRepository->finalizeScopes(
            [],
            Oauth2Module::GRANT_TYPE_IDENTIFIER_AUTH_CODE,
            $client,
            null
        );
    }

    /**
     * @return Oauth2ScopeRepositoryInterface
     * @throws \yii\base\InvalidConfigException
     */
    protected function getScopeRepository()
    {
        return Yii::createObject(Oauth2ScopeRepositoryInterface::class);
    }
}
