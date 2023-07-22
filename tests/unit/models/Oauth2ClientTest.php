<?php

namespace Yii2Oauth2ServerTests\unit\models;

use rhertogh\Yii2Oauth2Server\helpers\exceptions\EnvironmentVariableNotAllowedException;
use rhertogh\Yii2Oauth2Server\helpers\exceptions\EnvironmentVariableNotSetException;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2AccessToken;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\models\Oauth2ClientScope;
use rhertogh\Yii2Oauth2Server\models\Oauth2Scope;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Yii2Oauth2ServerTests\unit\models\_base\BaseOauth2ActiveRecordTest;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2IdentifierTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\Oauth2IdTestTrait;

/**
 * @covers \rhertogh\Yii2Oauth2Server\models\Oauth2Client
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2Client
 * @covers \rhertogh\Yii2Oauth2Server\models\base\Oauth2BaseActiveRecord
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\Oauth2ClientQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\queries\base\Oauth2BaseActiveQuery
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\BooleanBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\DateTimeBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\TimestampBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\BooleanBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\DateTimeBehavior
 * @covers \rhertogh\Yii2Oauth2Server\models\behaviors\TimestampBehavior
 *
 * @method Oauth2ClientInterface|ActiveRecord getMockModel(array $config = [])
 */

class Oauth2ClientTest extends BaseOauth2ActiveRecordTest
{
    use Oauth2IdTestTrait;
    use Oauth2IdentifierTestTrait;

    /**
     * @return class-string<Oauth2ClientInterface>
     */
    protected function getModelInterface()
    {
        return Oauth2ClientInterface::class;
    }

    /**
     * @return array[]
     * @see BaseOauth2ActiveRecordTest::testPersist()
     */
    public function persistTestProvider()
    {
        $this->mockConsoleApplication();
        return [
            // Valid.
            [
                [
                    'identifier' => 'my-test-client',
                    'type' => Oauth2ClientInterface::TYPE_CONFIDENTIAL,
                    'name' => 'my test client',
                    'redirect_uris' => 'https://my.test/uri',
                    'token_types' => Oauth2AccessToken::TYPE_BEARER,
                    'grant_types' => Oauth2Module::GRANT_TYPE_AUTH_CODE,
                ],
                true,
                function (Oauth2Client $model) {
                    $model->setSecret('my-test-secret', Oauth2Module::getInstance()->getCryptographer());
                }
            ],
            // Valid, multiple redirect URIs.
            [
                [
                    'identifier' => 'my-test-client',
                    'type' => Oauth2ClientInterface::TYPE_CONFIDENTIAL,
                    'name' => 'my test client',
                    'redirect_uris' => ['https://my.test/uri_1', 'https://my.test/uri_2'],
                    'token_types' => Oauth2AccessToken::TYPE_BEARER,
                    'grant_types' => Oauth2Module::GRANT_TYPE_AUTH_CODE,
                ],
                true,
                function (Oauth2Client $model) {
                    $model->setSecret('my-test-secret', Oauth2Module::getInstance()->getCryptographer());
                }
            ],
            // Invalid (missing secret for type confidential).
            [
                [
                    'identifier' => 'my-test-client',
                    'type' => Oauth2ClientInterface::TYPE_CONFIDENTIAL,
                    'name' => 'my test client',
                    'redirect_uris' => 'https://my.test/uri',
                    'token_types' => Oauth2AccessToken::TYPE_BEARER,
                    'grant_types' => Oauth2Module::GRANT_TYPE_AUTH_CODE,
                ],
                false,
            ],
        ];
    }

    /**
     * @return int[][]
     * @see Oauth2IdTestTrait::testFindByPk()
     */
    public function findByPkTestProvider()
    {
        return [[1003000]];
    }

    /**
     * @return string[][]
     * @see Oauth2IdentifierTestTrait::testFindByIdentifier()
     */
    public function findByIdentifierTestProvider()
    {
        return [['test-client-type-auth-code-valid']];
    }

    /**
     * @return array[]
     * @see Oauth2IdentifierTestTrait::testIdentifierExists()
     */
    public function identifierExistsProvider()
    {
        return [
            ['test-client-type-auth-code-valid', true],
            ['does-not-exists',                  false],
        ];
    }

    public function testIsEnabled()
    {
        $enabledClient = $this->getMockModel(); // enabled by default.
        $disabledClient = $this->getMockModel(['enabled' => 0]);

        $this->assertEquals(true, $enabledClient->isEnabled());
        $this->assertEquals(false, $disabledClient->isEnabled());
    }

    public function testPropertyGettersSetters()
    {
        $name = 'my-test-client';
        $type = Oauth2ClientInterface::TYPE_PUBLIC;
        $userAccountSelection = 1;
        $allowAuthCodeWithoutPkce = 2;
        $skipAuthorizationIfScopeIsAllowed = 3;
        $getScopeAccess = Oauth2ClientInterface::SCOPE_ACCESS_PERMISSIVE;
        $clientCredentialsGrantUserId = 5;
        $openIdConnectAllowOfflineAccessWithoutConsent = 6;
        $openIdConnectUserinfoEncryptedResponseAlg = 'RS256';
        $logoUri = 'https://test.com/logo';
        $termsOfServiceUri = 'https://test.com/tos';
        $contacts = 'admin@test.com';
        $endUsersMayAuthorizeClient = false;
        $minimumSecretLength = 123;

        $client = $this->getMockModel()
            ->setName($name)
            ->setType($type)
            ->setUserAccountSelection($userAccountSelection)
            ->setAllowAuthCodeWithoutPkce($allowAuthCodeWithoutPkce)
            ->setSkipAuthorizationIfScopeIsAllowed($skipAuthorizationIfScopeIsAllowed)
            ->setScopeAccess($getScopeAccess)
            ->setClientCredentialsGrantUserId($clientCredentialsGrantUserId)
            ->setOpenIdConnectAllowOfflineAccessWithoutConsent($openIdConnectAllowOfflineAccessWithoutConsent)
            ->setOpenIdConnectUserinfoEncryptedResponseAlg($openIdConnectUserinfoEncryptedResponseAlg)
            ->setLogoUri($logoUri)
            ->setTermsOfServiceUri($termsOfServiceUri)
            ->setContacts($contacts)
            ->setEndUsersMayAuthorizeClient($endUsersMayAuthorizeClient)
            ->setMinimumSecretLength($minimumSecretLength);

        // phpcs:disable Generic.Files.LineLength.TooLong -- readability actually better on single line
        $this->assertEquals($name, $client->getName());
        $this->assertEquals($type, $client->getType());
        $this->assertEquals($userAccountSelection, $client->getUserAccountSelection());
        $this->assertEquals($allowAuthCodeWithoutPkce, $client->isAuthCodeWithoutPkceAllowed());
        $this->assertEquals($skipAuthorizationIfScopeIsAllowed, $client->skipAuthorizationIfScopeIsAllowed());
        $this->assertEquals($getScopeAccess, $client->getScopeAccess());
        $this->assertEquals($clientCredentialsGrantUserId, $client->getClientCredentialsGrantUserId());
        $this->assertEquals($openIdConnectAllowOfflineAccessWithoutConsent, $client->getOpenIdConnectAllowOfflineAccessWithoutConsent());
        $this->assertEquals($openIdConnectUserinfoEncryptedResponseAlg, $client->getOpenIdConnectUserinfoEncryptedResponseAlg());
        $this->assertEquals($logoUri, $client->getLogoUri());
        $this->assertEquals($termsOfServiceUri, $client->getTermsOfServiceUri());
        $this->assertEquals($contacts, $client->getContacts());
        $this->assertEquals($endUsersMayAuthorizeClient, $client->endUsersMayAuthorizeClient());
        $this->assertEquals($minimumSecretLength, $client->getMinimumSecretLength());
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    public function testSetInvalidType()
    {
        $client = $this->getMockModel();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown type "999".');

        $client->setType(999);
    }

    public function testSetInvalidScopeAccess()
    {
        $client = $this->getMockModel();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown scope access "999".');

        $client->setScopeAccess(999);
    }

    public function testGetRedirectUri()
    {
        $envTestHost = 'test-host.com';
        $envTestPath = 'test/path';
        putenv('TEST_GET_REDIRECT_URI_HOST_NAME=' . $envTestHost);
        putenv('TEST_GET_REDIRECT_PATH=' . $envTestPath);

        $redirectUris = [
            'https://localhost/redirect_uri_1',
            'https://localhost/redirect_uri_2',
            '${DOES_NOT_EXIST}',
            'https://app.${TEST_GET_REDIRECT_URI_HOST_NAME}/${TEST_GET_REDIRECT_PATH}',
        ];
        $client = $this->getMockModel(['redirect_uris' => Json::encode($redirectUris)]);
        $client->setRedirectUriEnvVarConfig([
            'allowList' => ['*'],
            'denyList' => null,
            'parseNested' => true,
            'exceptionWhenNotSet' => false,
        ]);

        $expected = [
            'https://localhost/redirect_uri_1',
            'https://localhost/redirect_uri_2',
            // expect '${DOES_NOT_EXIST}' to be removed.
            "https://app.$envTestHost/$envTestPath",
        ];

        $this->assertEquals($expected, $client->getRedirectUri());
    }

    public function testGetRedirectUriByEnvVar()
    {
        $envTestUri = 'https://test-host1.com';
        putenv('TEST_GET_REDIRECT_URI=' . $envTestUri);

        $redirectUris = '${TEST_GET_REDIRECT_URI}';
        $client = $this->getMockModel(['redirect_uris' => Json::encode($redirectUris)]);
        $client->setRedirectUriEnvVarConfig([
            'allowList' => ['TEST_GET_REDIRECT_URI'],
        ]);

        $this->assertEquals([$envTestUri], $client->getRedirectUri());
    }

    public function testGetRedirectUriByEnvVarJsonString()
    {
        $envTestUri = 'https://test-host1.com';
        putenv('TEST_GET_REDIRECT_URI=' . Json::encode($envTestUri));

        $redirectUris = '${TEST_GET_REDIRECT_URI}';
        $client = $this->getMockModel(['redirect_uris' => Json::encode($redirectUris)]);
        $client->setRedirectUriEnvVarConfig([
            'allowList' => ['TEST_GET_REDIRECT_URI'],
        ]);

        $this->assertEquals([$envTestUri], $client->getRedirectUri());
    }

    public function testGetRedirectUriByEnvVarJsonArray()
    {
        $envTestUris = [
            'https://test-host1.com',
            'https://test-host2.com',
        ];
        putenv('TEST_GET_REDIRECT_URIS=' . Json::encode($envTestUris));

        $redirectUris = '${TEST_GET_REDIRECT_URIS}';
        $client = $this->getMockModel(['redirect_uris' => Json::encode($redirectUris)]);
        $client->setRedirectUriEnvVarConfig([
            'allowList' => ['TEST_GET_REDIRECT_URIS'],
        ]);

        $this->assertEquals($envTestUris, $client->getRedirectUri());
    }

    public function testGetRedirectUriWithEnvVarConfigNotSet()
    {
        $redirectUris = [
            'https://${DOES_NOT_EXIST}/test',
        ];
        $client = $this->getMockModel(['redirect_uris' => Json::encode($redirectUris)]);

        // Expect no parsing
        $this->assertEquals($redirectUris, $client->getRedirectUri());
    }

    public function testGetRedirectUriWithNotSetEnvVar()
    {
        $redirectUris = [
            'https://${DOES_NOT_EXIST}/test',
        ];
        $client = $this->getMockModel(['redirect_uris' => Json::encode($redirectUris)]);
        $client->setRedirectUriEnvVarConfig([
            'allowList' => ['*'],
        ]);

        $this->expectException(EnvironmentVariableNotSetException::class);
        $client->getRedirectUri();
    }

    public function testGetRedirectUriWithNotAllowedEnvVar()
    {
        $envTestHost = 'test-host.com';
        putenv('TEST_GET_REDIRECT_URI_HOST_NAME=' . $envTestHost);

        $redirectUris = [
            'https://app.${TEST_GET_REDIRECT_URI_HOST_NAME}/test',
        ];
        $client = $this->getMockModel(['redirect_uris' => Json::encode($redirectUris)]);
        $client->setRedirectUriEnvVarConfig([
            'allowList' => ['*'],
        ]);
        // Ensure test is working by first validating the setup
        $this->assertEquals(["https://app.$envTestHost/test"], $client->getRedirectUri());

        // Now ensure exception is thrown on "not allowed"
        $client->setRedirectUriEnvVarConfig([
            'allowList' => ['TEST'],
        ]);
        $this->expectException(EnvironmentVariableNotAllowedException::class);
        $client->getRedirectUri();
    }

    public function testRedirectUriNotSet()
    {
        $client = $this->getMockModel();

        $this->assertEquals([], $client->getRedirectUri());
    }

    public function testRedirectUriJsonEncodedString()
    {
        $client = $this->getMockModel(['redirect_uris' => Json::encode('https://test.com')]);

        $this->assertEquals(['https://test.com'], $client->getRedirectUri());
    }

    public function testRedirectUriInvalidType()
    {
        $client = $this->getMockModel(['redirect_uris' => Json::encode(true)]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('`redirect_uris` must be a JSON encoded string or array of strings.');

        $client->getRedirectUri();
    }

    public function testRedirectUriInvalidArrayElementType()
    {
        $client = $this->getMockModel(['redirect_uris' => Json::encode([true])]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('`redirect_uris` must be a JSON encoded string or array of strings.');

        $client->getRedirectUri();
    }

    public function testGetRedirectUriInvalidJson()
    {
        $client = $this->getMockModel([
            'id' => 1,
            'redirect_uris' => '[https://localhost/redirect_uri_1',
        ]);

        $this->expectExceptionMessage('Invalid json in redirect_uris for client 1');
        $client->getRedirectUri();
    }

    public function testSetRedirectUriString()
    {
        $client = $this->getMockModel();
        $redirectUri = 'https://localhost/redirect_uri_1';
        $client->setRedirectUri($redirectUri);
        $this->assertEquals(Json::encode([$redirectUri]), $client->redirect_uris);
    }

    public function testSetRedirectUriArray()
    {
        $client = $this->getMockModel();
        $redirectUris = [
            'https://localhost/redirect_uri_1',
            'https://localhost/redirect_uri_2',
        ];

        $client->setRedirectUri($redirectUris);
        $this->assertEquals(Json::encode($redirectUris), $client->redirect_uris);
    }

    public function testSetRedirectUriInvalidType()
    {
        $client = $this->getMockModel();
        $this->expectExceptionMessage('$uri must be a string or an array, got: object');
        $client->setRedirectUri(new \stdClass());
    }

    public function testSetRedirectUriInvalidArray()
    {
        $client = $this->getMockModel();
        $redirectUris = [
            'https://localhost/redirect_uri_1',
            new \stdClass(),
        ];

        $this->expectExceptionMessage('When $uri is an array, its values must be strings.');
        $client->setRedirectUri($redirectUris);
    }

    public function testIsVariableRedirectUriQueryAllowed()
    {
        $client = $this->getMockModel();
        // Should be `false` by default.
        $this->assertFalse($client->isVariableRedirectUriQueryAllowed());

        $client = $this->getMockModel(['allow_variable_redirect_uri_query' => true]);
        $this->assertTrue($client->isVariableRedirectUriQueryAllowed());
    }

    public function testSetAllowVariableRedirectUriQuery()
    {
        $client = $this->getMockModel();
        // Should be `false` by default.
        $this->assertFalse($client->isVariableRedirectUriQueryAllowed());

        $client->setAllowVariableRedirectUriQuery(true);
        $this->assertTrue($client->isVariableRedirectUriQueryAllowed());
    }

    public function testIsConfidential()
    {
        $confidentialClient = $this->getMockModel(['type' => Oauth2ClientInterface::TYPE_CONFIDENTIAL]);
        $publicClient = $this->getMockModel(['type' => Oauth2ClientInterface::TYPE_PUBLIC]);

        $this->assertEquals(true, $confidentialClient->isConfidential());
        $this->assertEquals(false, $publicClient->isConfidential());
    }

    public function testRotateStorageEncryptionKeys()
    {
        $oldKeyName = '2021-01-01';
        $newKeyName = '2022-01-01'; // default key name.
        $cryptographer = Oauth2Module::getInstance()->getCryptographer();

        Oauth2Client::updateAll(['old_secret' => new Expression('secret')], ['NOT', ['secret' => null]]);

        $clients = Oauth2Client::find()->andWhere(['NOT', ['secret' => null]])->all();

        foreach ($clients as $client) {
            $ciphertext = $client->getAttribute('secret');
            $this->assertStringStartsWith($oldKeyName . '::', $ciphertext);

            $ciphertext = $client->getAttribute('old_secret');
            $this->assertStringStartsWith($oldKeyName . '::', $ciphertext);
        }

        Oauth2Client::rotateStorageEncryptionKeys($cryptographer);

        foreach ($clients as $client) {
            $client->refresh();

            $ciphertext = $client->getAttribute('secret');
            $this->assertStringStartsWith($newKeyName . '::', $ciphertext);

            $ciphertext = $client->getAttribute('old_secret');
            $this->assertStringStartsWith($newKeyName . '::', $ciphertext);
        }
    }

    public function testRotateStorageEncryptionKeysFailure()
    {
        /** @var class-string<Oauth2Client> $modelClass */
        $modelClass = get_class(new class extends Oauth2Client {
            public static function tableName()
            {
                return 'oauth2_client';
            }

            public function persist($runValidation = true, $attributeNames = null)
            {
                throw new \Exception('test');
            }
        });

        $cryptographer = Oauth2Module::getInstance()->getCryptographer();

        $this->expectException(\Exception::class);
        $modelClass::rotateStorageEncryptionKeys($cryptographer);
    }

    public function testRotateStorageEncryptionKey()
    {
        $secret = 'my-test-secret';
        $oldKeyName = '2021-01-01';
        $newKeyName = '2022-01-01'; // default key name.
        $cryptographer = Oauth2Module::getInstance()->getCryptographer();
        $client = $this->getMockModel();
        $client->setSecret($secret, $cryptographer, null, $oldKeyName);

        $ciphertext = $client->getAttribute('secret');
        $this->assertStringStartsWith($oldKeyName . '::', $ciphertext);

        $client->rotateStorageEncryptionKey($cryptographer);
        $this->assertStringStartsWith($newKeyName . '::', $client->getAttribute('secret'));

        $this->assertEquals($secret, $client->getDecryptedSecret($cryptographer));
    }

    public function testSecret()
    {
        $oldKeyName = '2021-01-01';
        $newKeyName = '2022-01-01'; // default key name.

        $secret = 'my-test-secret';
        $secret2 = 'my-test-secret-2';
        $secret3 = 'my-test-secret-3';

        $cryptographer = Oauth2Module::getInstance()->getCryptographer();

        $client = $this->getMockModel();
        $client->setSecret($secret, $cryptographer);

        $ciphertext = $client->getAttribute('secret');
        $this->assertStringStartsWith($newKeyName . '::', $ciphertext);
        $this->assertFalse(strpos($secret, $client->getAttribute('secret')));
        $this->assertEquals($secret, $client->getDecryptedSecret($cryptographer));
        $this->assertTrue($client->validateSecret($secret, $cryptographer));
        $this->assertFalse($client->validateSecret('incorrect', $cryptographer));

        $client->setSecret($secret2, $cryptographer, new \DateInterval('P1D'), $oldKeyName);
        $ciphertext = $client->getAttribute('secret');
        $this->assertStringStartsWith($oldKeyName . '::', $ciphertext);
        $ciphertext = $client->getAttribute('old_secret');
        $this->assertStringStartsWith($oldKeyName . '::', $ciphertext);
        $this->assertEquals($secret2, $client->getDecryptedSecret($cryptographer));
        $this->assertTrue($client->validateSecret($secret2, $cryptographer)); // new secret.
        $this->assertTrue($client->validateSecret($secret, $cryptographer)); // old secret (which should still be valid).
        $this->assertFalse($client->validateSecret('incorrect', $cryptographer));

        $client->setSecret($secret3, $cryptographer, (new \DateTimeImmutable('yesterday')));
        $ciphertext = $client->getAttribute('secret');
        $this->assertStringStartsWith($newKeyName . '::', $ciphertext);
        $ciphertext = $client->getAttribute('old_secret');
        $this->assertStringStartsWith($newKeyName . '::', $ciphertext);
        $this->assertEquals($secret3, $client->getDecryptedSecret($cryptographer));
        $this->assertTrue($client->validateSecret($secret3, $cryptographer)); // new secret.
        $this->assertFalse($client->validateSecret($secret2, $cryptographer)); // old secret (which has expired).
        $this->assertFalse($client->validateSecret('incorrect', $cryptographer));
    }

    /**
     * @depends testSecret
     */
    public function testSecretLength()
    {
        $client = $this->getMockModel();

        $this->expectException(InvalidArgumentException::class);
        $client->setSecret('too-short', Oauth2Module::getInstance()->getCryptographer());
    }

    public function testSetSecretViaProperty()
    {
        $this->expectExceptionMessage('For security the "secret" property must be set via setSecret()');
        $this->getMockModel([
            'secret' => 'test',
        ]);
    }

    /**
     * @depends testSecret
     */
    public function testSetSecretForTypeNonConfidential()
    {
        $cryptographer = Oauth2Module::getInstance()->getCryptographer();
        $client = $this->getMockModel([
            'type' => Oauth2ClientInterface::TYPE_CONFIDENTIAL,
        ]);

        $client->setSecret('my-test-secret', $cryptographer);

        $this->assertNotEmpty($client->secret);
        $client->type = Oauth2ClientInterface::TYPE_PUBLIC;
        $client->setSecret(null, $cryptographer); // Setting secret to `null` on public type should be allowed.
        $this->assertNull($client->secret);

        $this->expectExceptionMessage('The secret for a non-confidential client can only be set to `null`');
        $client->setSecret('my-test-secret', $cryptographer);
    }

    public function testValidateGrantType()
    {
        $client = $this->getMockModel();
        $client->setGrantTypes(
            Oauth2Module::GRANT_TYPE_AUTH_CODE | Oauth2Module::GRANT_TYPE_CLIENT_CREDENTIALS
        );

        $this->assertEquals(true, $client->validateGrantType('authorization_code'));
        $this->assertEquals(true, $client->validateGrantType('client_credentials'));
        $this->assertEquals(false, $client->validateGrantType('refresh_token'));
        $this->assertEquals(false, $client->validateGrantType('password'));
    }

    /**
     * @depends testValidateGrantType
     */
    public function testValidateGrantTypeWithUnknownType()
    {
        $client = $this->getMockModel([
            'grant_types' => Oauth2Module::GRANT_TYPE_AUTH_CODE | Oauth2Module::GRANT_TYPE_CLIENT_CREDENTIALS
        ]);

        $this->expectExceptionMessage('Unknown grant type "does-not-exist"');
        $client->validateGrantType('does-not-exist');
    }

    public function testSetInvalidGrantTypes()
    {
        $this->expectExceptionMessage('Unknown Grant Type ID: 8192');
        $this->getMockModel()->setGrantTypes(
            9999
        );
    }

    /**
     * @dataProvider validateAuthRequestScopesProvider
     */
    public function testValidateAuthRequestScopes(
        $requestedScopeIdentifiers,
        $scopeAccess,
        $expected,
        $expectedUnauthorizedScopes
    ) {
        $client = $this->getMockModel([
            'id' => 1003000,
            'scope_access' => $scopeAccess,
        ]);

        $this->assertEquals(
            $expected,
            $client->validateAuthRequestScopes($requestedScopeIdentifiers, $unauthorizedScopes)
        );
        $this->assertEquals($expectedUnauthorizedScopes, $unauthorizedScopes);
    }

    public function validateAuthRequestScopesProvider()
    {
        // phpcs:disable Generic.Files.LineLength.TooLong -- readability acually better on single line
        return [
            [['user.username.read', 'user.email_address.read'], Oauth2Client::SCOPE_ACCESS_STRICT, true, []],
            [['user.username.read', 'user.email_address.read'], Oauth2Client::SCOPE_ACCESS_STRICT_QUIET, true, []],
            [['user.username.read', 'disabled-scope'], Oauth2Client::SCOPE_ACCESS_STRICT, false, ['disabled-scope']],
            [['user.email_address.read', 'disabled-scope-for-client'], Oauth2Client::SCOPE_ACCESS_STRICT, false, ['disabled-scope-for-client']],
            [['defined-but-not-assigned'], Oauth2Client::SCOPE_ACCESS_STRICT, false, ['defined-but-not-assigned']],
            [['non-existing'], Oauth2Client::SCOPE_ACCESS_STRICT, false, ['non-existing']],

            [['defined-but-not-assigned'], Oauth2Client::SCOPE_ACCESS_STRICT_QUIET, true, []],
            [['non-existing'], Oauth2Client::SCOPE_ACCESS_STRICT_QUIET, true, []],

            [['defined-but-not-assigned'], Oauth2Client::SCOPE_ACCESS_PERMISSIVE, true, []],
            [['non-existing'], Oauth2Client::SCOPE_ACCESS_PERMISSIVE, false, ['non-existing']],
        ];
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    /**
     * @dataProvider getAllowedScopesProvider
     */
    public function testGetAllowedScopes($clientId, $requestedScopeIdentifiers, $scopeAccess, $expectedScopes)
    {
        $client = $this->getMockModel([
            'id' => $clientId,
            'scope_access' => $scopeAccess,
        ]);

        $allowedScopeIdentifiers = array_column($client->getAllowedScopes($requestedScopeIdentifiers), 'identifier');
        $this->assertEquals($expectedScopes, $allowedScopeIdentifiers);
    }

    public function getAllowedScopesProvider()
    {
        return [
            [
                1003000,
                [],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                [
                    'user.id.read',
                    'applied-by-default',
                    'applied-by-default-for-client',
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                    'applied-by-default-by-client-not-required-for-client',
                    'applied-by-default-if-requested',
                    'applied-by-default-if-requested-for-client',
                ],
            ],
            [
                1003000,
                [],
                Oauth2Client::SCOPE_ACCESS_PERMISSIVE,
                [
                    'user.id.read',
                    'applied-by-default',
                    'applied-by-default-for-client',
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                    'applied-automatically-by-default-not-assigned',
                    'applied-automatically-by-default-not-assigned-not-required',
                    'applied-by-default-by-client-not-required-for-client',
                    'applied-by-default-if-requested',
                    'applied-by-default-if-requested-for-client',
                ],
            ],
            [
                1003000,
                [
                    'user.username.read',
                    'user.email_address.read',
                    'defined-but-not-assigned',
                    'disabled-scope',
                    'disabled-scope-for-client',
                    'non-existing',
                ],
                Oauth2Client::SCOPE_ACCESS_STRICT,
                [
                    'user.id.read',
                    'user.username.read',
                    'user.email_address.read',
                    'applied-by-default',
                    'applied-by-default-for-client',
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                    'applied-by-default-by-client-not-required-for-client',
                    'applied-by-default-if-requested',
                    'applied-by-default-if-requested-for-client',
                ],
            ],
            [
                1003000,
                [
                    'user.username.read',
                    'user.email_address.read',
                    'defined-but-not-assigned',
                    'disabled-scope',
                    'disabled-scope-for-client',
                    'non-existing',
                ],
                Oauth2Client::SCOPE_ACCESS_PERMISSIVE,
                [
                    'user.id.read',
                    'user.username.read',
                    'user.email_address.read',
                    'defined-but-not-assigned',
                    'applied-by-default',
                    'applied-by-default-for-client',
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                    'applied-automatically-by-default-not-assigned',
                    'applied-automatically-by-default-not-assigned-not-required',
                    'applied-by-default-by-client-not-required-for-client',
                    'applied-by-default-if-requested',
                    'applied-by-default-if-requested-for-client',
                ],
            ],
        ];
    }

    public function testValidateAuthRequestScopesInvalid()
    {
        $client = $this->getMockModel([
            'id' => 1003000,
            'scope_access' => 999999999,
        ]);

        $this->expectExceptionMessage('Unknown scope_access: "999999999".');
        $client->validateAuthRequestScopes(['user.username.read']);
    }

    /**
     * @dataProvider syncClientScopesProvider
     * @param int $clientId
     * @param string|string[]|array[]|Oauth2ClientScopeInterface[]|Oauth2ScopeInterface[]|null $newScopes
     * @param array $expected
     */
    public function testSyncClientScopes($clientId, $newScopes, $expected)
    {
        $client = Oauth2Client::findByPk($clientId);

        if (is_callable($newScopes)) {
            $newScopes = call_user_func($newScopes);
        }

        $syncResult = $client->syncClientScopes($newScopes, Oauth2Module::getInstance()->getScopeRepository());

        $unaffected = ArrayHelper::getColumn($syncResult['unaffected'], 'scope.identifier', false);
        $this->assertEquals($expected['unaffected'], $unaffected);

        $new = ArrayHelper::getColumn($syncResult['new'], 'scope.identifier', false);
        $this->assertEquals($expected['new'], $new);

        $updated = ArrayHelper::getColumn($syncResult['updated'], 'scope.identifier', false);
        $this->assertEquals($expected['updated'], $updated);

        $deleted = ArrayHelper::getColumn($syncResult['deleted'], 'scope.identifier', false);
        $this->assertEquals($expected['deleted'], $deleted);
    }

    public function syncClientScopesProvider()
    {

        return [
            'as string' => [
                'clientId' => 1003006,
                'scopes' => 'user.id.read openid profile email phone user.username.read',
                'expected' => [
                    'unaffected' => ['openid', 'profile', 'email', 'phone'],
                    'new' => ['user.id.read', 'user.username.read'],
                    'updated' => [],
                    'deleted' => ['address', 'offline_access'],
                ],
            ],
            'as string[]' => [
                'clientId' => 1003006,
                'scopes' => ['user.id.read', 'openid', 'profile', 'email', 'phone', 'user.username.read'],
                'expected' => [
                    'unaffected' => ['openid', 'profile', 'email', 'phone'],
                    'new' => ['user.id.read', 'user.username.read'],
                    'updated' => [],
                    'deleted' => ['address', 'offline_access'],
                ],
            ],
            'as array[]' => [
                'clientId' => 1003006,
                'scopes' => [
                    'user.id.read' => [], // new, identifier as key.
                    'openid' => [], // unaffected, identifier as key.
                    'profile' => [ // updated, identifier as key.
                        'applied_by_default' => Oauth2ScopeInterface::APPLIED_BY_DEFAULT_AUTOMATICALLY,
                    ],
                    [ // unaffected, scope id as column.
                        'scope_id' => 3, // identifier: 'email'.
                    ],
                    [ // updated, scope id as column
                        'scope_id' => 5, // identifier: 'phone'.
                        'applied_by_default' => Oauth2ScopeInterface::APPLIED_BY_DEFAULT_AUTOMATICALLY,
                    ],
                    [ // new, scope id as column
                        'scope_id' => 1005001, // identifier: 'user.username.read'.
                    ],
                ],
                'expected' => [
                    'unaffected' => ['openid', 'email'],
                    'new' => ['user.id.read', 'user.username.read'],
                    'updated' => ['profile', 'phone'],
                    'deleted' => ['address', 'offline_access'],
                ],
            ],
            'as Oauth2ClientScope[]' => [
                'clientId' => 1003006,
                'scopes' => function () {
                    $currentClientScopes = Oauth2ClientScope::find()
                        ->andWhere([
                            'client_id' => 1003006,
                        ])
                        ->indexBy('scope_id')
                        ->all();

                    unset(
                        $currentClientScopes[2], // 'profile'
                        $currentClientScopes[3], // 'email'
                    );

                    // 'offline_access'
                    $currentClientScopes[6]->required_on_authorization = true;

                    return ArrayHelper::merge(
                        [new Oauth2ClientScope([
                            'scope_id' => 1005003, // 'user.enabled.read'
                        ])],
                        $currentClientScopes,
                        [new Oauth2ClientScope([
                            'scope_id' => 1005002, // 'user.email_address.read'
                            'applied_by_default' => Oauth2ScopeInterface::APPLIED_BY_DEFAULT_AUTOMATICALLY,
                        ])],
                    );
                },
                'expected' => [
                    'unaffected' => ['openid', 'address', 'phone'],
                    'new' => ['user.enabled.read', 'user.email_address.read'],
                    'updated' => ['offline_access'],
                    'deleted' => ['profile', 'email'],
                ],
            ],
            'as Oauth2Scope[]' => [
                'clientId' => 1003006,
                'scopes' => function () {
                    return Oauth2Scope::findAll([
                        'identifier' => ['openid', 'profile', 'address', 'offline_access', 'user.email_address.read'],
                    ]);
                },
                'expected' => [
                    'unaffected' => ['openid', 'profile', 'address', 'offline_access'],
                    'new' => ['user.email_address.read'],
                    'updated' => [],
                    'deleted' => ['email', 'phone'],
                ],
            ],
            'as null' => [
                'clientId' => 1003006,
                'scopes' => null,
                'expected' => [
                    'unaffected' => [],
                    'new' => [],
                    'updated' => [],
                    'deleted' => [
                        'openid',
                        'profile',
                        'email',
                        'address',
                        'phone',
                        'offline_access',
                    ],
                ],
            ],
        ];
    }

    public function testSyncClientScopesInvalidScopes()
    {
        $client = $this->getMockModel();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$scopes must be a string, an array or null.');

        $client->syncClientScopes(new \stdClass(), Oauth2Module::getInstance()->getScopeRepository());
    }

    public function testSyncClientScopesInvalidScopesElement()
    {
        $client = $this->getMockModel();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('If $scopes is an array, its values must be a string, array or an instance of '
            . Oauth2ClientScopeInterface::class . ' or ' . Oauth2ScopeInterface::class . '.');

        $client->syncClientScopes([new \stdClass()], Oauth2Module::getInstance()->getScopeRepository());
    }

    public function testSyncClientScopesUnknownScope()
    {
        $client = $this->getMockModel();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No scope with identifier "does_not_exist" found.');

        $client->syncClientScopes('does_not_exist', Oauth2Module::getInstance()->getScopeRepository());
    }

    public function testSyncClientScopesEmptyOauth2Scope()
    {
        $client = $this->getMockModel();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Element 0 in $scope should specify either the scope id or its identifier.');

        $client->syncClientScopes([new Oauth2Scope()], Oauth2Module::getInstance()->getScopeRepository());
    }

    public function testSyncClientScopesRollbackOnError()
    {
        $client = $this->getMockModel();

        $mockClientScope = new class extends Oauth2ClientScope {
            public static $tableName = 'oauth2_client_scope';
            public function persist($runValidation = true, $attributeNames = null)
            {
                throw new \Exception('testSyncClientScopesRollbackOnError');
            }
        };

        $this->expectExceptionMessage('testSyncClientScopesRollbackOnError');

        try {
            $client->syncClientScopes([$mockClientScope], Oauth2Module::getInstance()->getScopeRepository());
        } finally {
            $origClientScopes = $client->getClientScopes()->all();
            $this->assertEquals($origClientScopes, $client->getClientScopes()->all());
        }
    }
}
