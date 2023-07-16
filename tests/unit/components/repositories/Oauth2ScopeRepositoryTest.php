<?php

namespace Yii2Oauth2ServerTests\unit\components\repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2ScopeRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\models\Oauth2Scope;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\InvalidConfigException;
use Yii2Oauth2ServerTests\unit\components\repositories\_base\BaseOauth2RepositoryTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\repositories\Oauth2ScopeRepository
 *
 * @method class-string<Oauth2ClientInterface> getModelClass()
 */
class Oauth2ScopeRepositoryTest extends BaseOauth2RepositoryTest
{
    protected const TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NONE = 0;
    protected const TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NOT_ALLOWED = 1;

    /**
     * @return class-string<Oauth2ScopeInterface>
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
        $expectedScopes,
        $expectedError
    ) {
        $scopeRepository = $this->getScopeRepository();

        $client = Oauth2Client::findOne($clientId);
        $client->scope_access = $scopeAccess;
        $requestedScopes = Oauth2Scope::findAll(['identifier' => $requestedScopeIdentifiers]);
        if (count($requestedScopes) !== count($requestedScopeIdentifiers)) {
            throw new \InvalidArgumentException('Not all scopes could be found.');
        }

        if ($expectedError === static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NOT_ALLOWED) {
            $this->expectExceptionMessage('The requested scope is not allowed for the specified client.');
        }

        $finalizedScopes = array_column(
            $scopeRepository->finalizeScopes(
                $requestedScopes,
                $grantType,
                $client,
                $userId
            ),
            'identifier'
        );

        if ($expectedError === static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NONE) {
            $this->assertEquals($expectedScopes, $finalizedScopes);
        } else {
            throw new InvalidConfigException('Unknown expected error type: ' . $expectedError);
        }
    }

    /**
     * @return array{
     *             clientId: int,
     *             userId: int,
     *             requestedScopeIdentifiers: string[],
     *             scopeAccess: int,
     *             grantType: string,
     *             expectedScope: string[],
     *             expectedError: int,
     *         }[]
     */
    public function finalizeScopesProvider()
    {
        // ToDo: define correct test cases after discussing scope with upstream library.
        return [
            // Not assigned Scope
            // Note: The scope for the Auth Code Grant is checked during authorization.
            'strict_auth_code_not_assigned' => [
                1003000,
                123,
                [
                    // since the scope is checked during authorization we simply don't return it.
                    'defined-but-not-assigned',
                ],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_AUTH_CODE,
                [
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                ],
                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NONE,
            ],
            // Note: The scope for the Implicit Grant is checked during authorization.
            'strict_implicit_not_assigned' => [
                1003000,
                123,
                [
                    // since the scope is checked during authorization we simply don't return it.
                    'defined-but-not-assigned',
                ],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_IMPLICIT,
                [
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                ],
                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NONE,
            ],
            'strict_client_credentials_not_assigned' => [
                1003000,
                123,
                [
                    'defined-but-not-assigned',
                ],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS,
                [],
                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NOT_ALLOWED,
            ],
            'strict_password_not_assigned' => [
                1003000,
                123,
                [
                    'defined-but-not-assigned',
                ],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_PASSWORD,
                [],
                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NOT_ALLOWED,
            ],

            // Disabled Scope
            // Note: The scope for the Auth Code Grant is checked during authorization.
            'strict_auth_code_disabled' => [
                1003000,
                123,
                [
                    // since the scope is checked during authorization we simply don't return it.
                    'disabled-scope',
                ],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_AUTH_CODE,
                [
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                ],
                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NONE,
            ],
            // Note: The scope for the Implicit Grant is checked during authorization.
            'strict_implicit_disabled' => [
                1003000,
                123,
                [
                    // since the scope is checked during authorization we simply don't return it.
                    'disabled-scope',
                ],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_IMPLICIT,
                [
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                ],
                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NONE,
            ],
            'strict_client_credentials_disabled' => [
                1003000,
                123,
                [
                    'disabled-scope',
                ],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS,
                [],
                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NOT_ALLOWED,
            ],
            'strict_password_disabled' => [
                1003000,
                123,
                [
                    'disabled-scope',
                ],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_PASSWORD,
                [],
                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NOT_ALLOWED,
            ],


            'strict_auth_code_default' => [
                1003000,
                123,
                [],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_AUTH_CODE,
                [
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                ],
                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NONE,
            ],
            'permissive_auth_code_default' => [
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
                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NONE,
            ],

            'strict_client_credentials_disabled_for_client' => [
                1003000,
                123,
                [
                    'disabled-scope-for-client',
                ],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS,
                [],
                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NOT_ALLOWED,
            ],
            'strict_client_credentials_defined_but_not_assigned' => [
                1003000,
                123,
                [
                    'defined-but-not-assigned',
                ],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                Oauth2Module::GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS,
                [],
                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NOT_ALLOWED,
            ],
//            'strict_client_credentials_custom' => [
//                1003000,
//                null,
//                [
//                    'user.username.read',
//                    'user.email_address.read',
//                ],
//                Oauth2Client::SCOPE_ACCESS_STRICT,
//                Oauth2Module::GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS,
//                [
//                    'user.id.read',
//                    'user.username.read',
//                    'user.email_address.read',
//                    'applied-by-default',
//                    'applied-by-default-for-client',
//                    'applied-automatically-by-default',
//                    'applied-automatically-by-default-for-client',
//                    'applied-by-default-by-client-not-required-for-client',
//                ],
//                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NONE,
//            ],
//            'permissive_client_credentials_custom' => [
//                1003000,
//                null,
//                [
//                    'user.username.read',
//                    'user.email_address.read',
//                    'defined-but-not-assigned',
//                    'disabled-scope',
//                    'disabled-scope-for-client',
//                    'non-existing',
//                ],
//                Oauth2Client::SCOPE_ACCESS_PERMISSIVE,
//                Oauth2Module::GRANT_TYPE_IDENTIFIER_CLIENT_CREDENTIALS,
//                [
//                    'user.id.read',
//                    'user.username.read',
//                    'user.email_address.read',
//                    'defined-but-not-assigned',
//                    'applied-by-default',
//                    'applied-by-default-for-client',
//                    'applied-automatically-by-default',
//                    'applied-automatically-by-default-for-client',
//                    'applied-automatically-by-default-not-assigned',
//                    'applied-automatically-by-default-not-assigned-not-required',
//                    'applied-by-default-by-client-not-required-for-client',
//                ],
//                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NONE,
//            ],
//            'strict_auth_code_custom' => [
//                1003002,
//                124,
//                [
//                    'user.username.read',
//                    'user.email_address.read',
//                    'defined-but-not-assigned',
//                    'disabled-scope',
//                    'disabled-scope-for-client',
//                    'non-existing',
//                ],
//                Oauth2Client::SCOPE_ACCESS_STRICT,
//                Oauth2Module::GRANT_TYPE_IDENTIFIER_AUTH_CODE,
//                [
//                    'user.id.read',
//                    'user.username.read',
//                    'user.email_address.read',
//                    'applied-automatically-by-default-for-client',
//                ],
//                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NONE,
//            ],
//            'permissive_auth_code_custom' => [
//                1003002,
//                124,
//                [
//                    'user.username.read',
//                    'user.email_address.read',
//                    'defined-but-not-assigned',
//                    'disabled-scope',
//                    'disabled-scope-for-client',
//                    'non-existing',
//                ],
//                Oauth2Client::SCOPE_ACCESS_PERMISSIVE,
//                Oauth2Module::GRANT_TYPE_IDENTIFIER_AUTH_CODE,
//                [
//                    'user.id.read',
//                    'user.username.read',
//                    'user.email_address.read',
//                    'applied-automatically-by-default-for-client',
//                    'applied-automatically-by-default-not-assigned',
//                    'applied-automatically-by-default-not-assigned-not-required',
//                ],
//                static::TEST_FINALIZE_SCOPES_EXPECTED_ERROR_NONE,
//            ],
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
