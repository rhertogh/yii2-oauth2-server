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
 */

class Oauth2ClientTest extends BaseOauth2ActiveRecordTest
{
    use Oauth2IdTestTrait;
    use Oauth2IdentifierTestTrait;

    /**
     * @param array $config
     * @return Oauth2ClientInterface
     * @throws InvalidConfigException
     */
    protected function getMockModel($config = [])
    {
        return parent::getMockModel($config)->setModule(Oauth2Module::getInstance());
    }

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
        $allowGenericScopes = true;
        $exceptionOnInvalidScope = true;
        $clientCredentialsGrantUserId = 5;
        $openIdConnectAllowOfflineAccessWithoutConsent = 6;
        $openIdConnectUserinfoEncryptedResponseAlg = 'RS256';
        $logoUri = 'https://test.com/logo';
        $termsOfServiceUri = 'https://test.com/tos';
        $contacts = 'admin@test.com';
        $endUsersMayAuthorizeClient = false;
        $minimumSecretLength = 123;
        $envVarConfig = [
            'redirectUris' => [
                'allowList' => ['test'],
                'denyList' => ['not_allowed'],
                'parseNested' => true,
                'exceptionWhenNotSet' => false,
                'exceptionWhenNotAllowed' => false,
            ],
            'secrets' => [
                'allowList' => ['test'],
                'denyList' => ['not_allowed'],
                'parseNested' => true,
                'exceptionWhenNotSet' => false,
                'exceptionWhenNotAllowed' => false,
            ],
        ];

        $client = $this->getMockModel()
            ->setName($name)
            ->setType($type)
            ->setEnvVarConfig($envVarConfig)
            ->setUserAccountSelection($userAccountSelection)
            ->setAllowAuthCodeWithoutPkce($allowAuthCodeWithoutPkce)
            ->setSkipAuthorizationIfScopeIsAllowed($skipAuthorizationIfScopeIsAllowed)
            ->setAllowGenericScopes($allowGenericScopes)
            ->setExceptionOnInvalidScope($exceptionOnInvalidScope)
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
        $this->assertEquals($envVarConfig, $client->getEnvVarConfig());
        $this->assertEquals($userAccountSelection, $client->getUserAccountSelection());
        $this->assertEquals($allowAuthCodeWithoutPkce, $client->isAuthCodeWithoutPkceAllowed());
        $this->assertEquals($skipAuthorizationIfScopeIsAllowed, $client->skipAuthorizationIfScopeIsAllowed());
        $this->assertEquals($allowGenericScopes, $client->getAllowGenericScopes());
        $this->assertEquals($exceptionOnInvalidScope, $client->getExceptionOnInvalidScope());
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

    public function testGetRedirectUrisEnvVarConfig()
    {
        $moduleLevelConfig = [
            'allowList' => ['test_module'],
            'denyList' => ['not_allowed_module'],
            'parseNested' => true,
            'exceptionWhenNotSet' => true,
            'exceptionWhenNotAllowed' => true,
        ];
        $clientLevelConfig = [
            'allowList' => ['test_module'],
            'denyList' => ['not_allowed_module'],
            'parseNested' => true,
            'exceptionWhenNotSet' => true,
            'exceptionWhenNotAllowed' => true,
        ];

        $client = $this->getMockModel();

        // No config set.
        $this->assertNull($client->getRedirectUrisEnvVarConfig());

        // Module config set.
        Oauth2Module::getInstance()->clientRedirectUrisEnvVarConfig = $moduleLevelConfig;
        $this->assertEquals($moduleLevelConfig, $client->getRedirectUrisEnvVarConfig());

        // Client config set.
        $client->setEnvVarConfig(['redirectUris' => $clientLevelConfig]);
        $this->assertEquals($clientLevelConfig, $client->getRedirectUrisEnvVarConfig());

        // Client config reset, expect fallback to module.
        $client->setEnvVarConfig(null);
        $this->assertEquals($moduleLevelConfig, $client->getRedirectUrisEnvVarConfig());
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
        $client->setEnvVarConfig([
            'redirectUris' => [
                'allowList' => ['*'],
                'denyList' => null,
                'parseNested' => true,
                'exceptionWhenNotSet' => false,
            ],
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
        $client->setEnvVarConfig([
            'redirectUris' => [
                'allowList' => ['TEST_GET_REDIRECT_URI'],
            ],
        ]);

        $this->assertEquals([$envTestUri], $client->getRedirectUri());
    }

    public function testGetRedirectUriByEnvVarJsonString()
    {
        $envTestUri = 'https://test-host1.com';
        putenv('TEST_GET_REDIRECT_URI=' . Json::encode($envTestUri));

        $redirectUris = '${TEST_GET_REDIRECT_URI}';
        $client = $this->getMockModel(['redirect_uris' => Json::encode($redirectUris)]);
        $client->setEnvVarConfig([
            'redirectUris' => [
                'allowList' => ['TEST_GET_REDIRECT_URI'],
            ],
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
        $client->setEnvVarConfig([
            'redirectUris' => [
                'allowList' => ['TEST_GET_REDIRECT_URIS'],
            ],
        ]);

        $this->assertEquals($envTestUris, $client->getRedirectUri());
    }

    public function testGetRedirectUriWithEnvVarConfigNotSet()
    {
        $redirectUris = [
            'https://${DOES_NOT_EXIST}/test',
        ];
        $client = $this->getMockModel(['redirect_uris' => Json::encode($redirectUris)]);

        // Expect no parsing.
        $this->assertEquals($redirectUris, $client->getRedirectUri());
    }

    public function testGetRedirectUriWithNotSetEnvVar()
    {
        $redirectUris = [
            'https://${DOES_NOT_EXIST}/test',
        ];
        $client = $this->getMockModel(['redirect_uris' => Json::encode($redirectUris)]);
        $client->setEnvVarConfig([
            'redirectUris' => [
                'allowList' => ['*'],
            ],
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
        $client->setEnvVarConfig([
            'redirectUris' => [
                'allowList' => ['*'],
            ],
        ]);
        // Ensure test is working by first validating the setup.
        $this->assertEquals(["https://app.$envTestHost/test"], $client->getRedirectUri());

        // Now ensure exception is thrown on "not allowed".
        $client->setEnvVarConfig([
            'redirectUris' => [
                'allowList' => ['TEST'],
            ],
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

        $this->expectExceptionMessage('Invalid json in `redirect_uris` for client 1');
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

    public function testSecretViaEnvVar()
    {
        $secret = 'iWjgoZNcof';
        $secretEnvVarName = 'SECRET_VIA_ENV_VAR_SECRET';
        $secretEnvVarValue = '2022-01-01::3vUCAL1/Qh2FzmH99AUxSxc/y7w2DSGKbv8PC5Qxl46S70xVB7oBz5m3YNkD0dDByxPgAlAKimVlRr98+oIqUo2McahxkqNkBATBLcNPrPzTfCDxH8ZA++ZcsDjBhA==';

        $oldSecret = 'bvuH7joLZN';
        $oldSecretEnvVarName = 'SECRET_VIA_ENV_VAR_OLD_SECRET';
        $oldSecretEnvVarValue = '2021-01-01::3vUCALFgiRV0PVg7owgeUO2+hpeVsrwVx0OViUcbf5QcoB6wmzjq5rcWpJX8l6T5qvNFMRBILtCTKhW0w0o+KpH4oJmzLZL4If3wvmWSremPutwc1jn38w2MSUdZWg==';

        $client = $this->getMockModel();

        putenv($secretEnvVarName . '=' . $secretEnvVarValue);
        putenv($oldSecretEnvVarName . '=' . $oldSecretEnvVarValue);
        $client->setSecretsAsEnvVars($secretEnvVarName, $oldSecretEnvVarName, new \DateInterval('P1D'));
        $client->setEnvVarConfig([
            'secrets' => [
                'allowList' => ['SECRET_VIA_ENV_VAR_SECRET', 'SECRET_VIA_ENV_VAR_OLD_SECRET'],
            ],
        ]);

        $cryptographer = Oauth2Module::getInstance()->getCryptographer();

        $this->assertTrue($client->validateSecret($secret, $cryptographer));
        $this->assertTrue($client->validateSecret($oldSecret, $cryptographer));

        $client->setEnvVarConfig([
            'secrets' => [
                'allowList' => ['DOES_NOT_EXIST'],
            ],
        ]);

        $this->expectException(EnvironmentVariableNotAllowedException::class);
        $this->expectExceptionMessage('Usage of environment variable "SECRET_VIA_ENV_VAR_SECRET" is not allowed');
        $this->assertTrue($client->validateSecret($secret, $cryptographer));
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
        $allowGenericScopes,
        $expected,
        $expectedUnknownScopes,
        $expectedUnauthorizedScopes
    ) {
        $client = $this->getMockModel([
            'id' => 1003000,
            'allow_generic_scopes' => $allowGenericScopes,
        ]);

        $this->assertEquals(
            $expected,
            $client->validateAuthRequestScopes($requestedScopeIdentifiers, $unknownScopes, $unauthorizedScopes)
        );
        $this->assertEquals($expectedUnknownScopes, $unknownScopes);
        $this->assertEquals($expectedUnauthorizedScopes, $unauthorizedScopes);
    }

    /**
     * @return array{
     *             scopes: string[],
     *             allowGenericScopes: bool,
     *             expected: bool,
     *             expectedUnknownScopes: string[],
     *             expectedUnauthorizedScopes: string[],
     *         }[]
     */
    public function validateAuthRequestScopesProvider()
    {
        return [
            'strict_ok' => [
                ['user.username.read', 'user.email_address.read'],
                false,
                true,
                [],
                [],
            ],
            'strict_invalid_disabled' => [
                ['user.username.read', 'disabled-scope'],
                false,
                false,
                [],
                ['disabled-scope'],
            ],
            'strict_invalid_disabled_for_client' => [
                ['user.email_address.read', 'disabled-scope-for-client'],
                false,
                false,
                [],
                ['disabled-scope-for-client'],
            ],
            'strict_invalid_not_assigned' => [
                ['defined-but-not-assigned'],
                false,
                false,
                [],
                ['defined-but-not-assigned'],
            ],
            'strict_non_existing' => [
                ['non-existing'],
                false,
                false,
                ['non-existing'],
                [],
            ],

            'generic_ok' => [
                ['defined-but-not-assigned'],
                true,
                true,
                [],
                [],
            ],
            'generic_non_existing' => [
                ['non-existing'],
                true,
                false,
                ['non-existing'],
                [],
            ],
        ];
    }

    /**
     * @dataProvider getAllowedScopesProvider
     */
    public function testGetAllowedScopes(
        $clientId,
        $requestedScopeIdentifiers,
        $allowGenericScopes,
        $expectedScopes
    ) {
        $client = $this->getMockModel([
            'id' => $clientId,
            'allow_generic_scopes' => $allowGenericScopes,
        ]);

        $allowedScopeIdentifiers = array_column($client->getAllowedScopes($requestedScopeIdentifiers), 'identifier');
        $this->assertEquals($expectedScopes, $allowedScopeIdentifiers);
    }

    /**
     * @return array{
     *             clientId: int,
     *             requestedScopeIdentifiers: string[],
     *             allowGenericScopes: bool,
     *             expectedScopes: string[],
     *         }[]
     */
    public function getAllowedScopesProvider()
    {
        return [
            'default_only_strict' => [
                1003000,
                [],
                false,
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
            'default_only_generic' => [
                1003000,
                [],
                true,
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
            'all_strict' => [
                1003000,
                true, // Request all possible scopes
                false, // only strictly defined scopes
                [
                    'user.id.read',
                    'user.username.read',
                    'user.email_address.read',
                    'user.enabled.read',
                    'user.created_at.read',
                    'user.updated_at.read',
                    'applied-by-default',
                    'applied-by-default-for-client',
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                    'applied-by-default-by-client-not-required-for-client',
                    'pre-assigned-for-user-test',
                    'not-required',
                    'applied-by-default-if-requested',
                    'applied-by-default-if-requested-for-client',
                ],
            ],
            'all_generic' => [
                1003000,
                true, // Request all possible scopes
                true, // include generic scopes (not only directly connected to the client)
                [
                    'openid',
                    'profile',
                    'email',
                    'address',
                    'phone',
                    'offline_access',
                    'user.id.read',
                    'user.username.read',
                    'user.email_address.read',
                    'user.enabled.read',
                    'user.created_at.read',
                    'user.updated_at.read',
                    'defined-but-not-assigned',
                    'applied-by-default',
                    'applied-by-default-for-client',
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                    'applied-automatically-by-default-not-assigned',
                    'applied-automatically-by-default-not-assigned-not-required',
                    'applied-by-default-by-client-not-required-for-client',
                    'pre-assigned-for-user-test',
                    'not-required',
                    'not-required-has-been-rejected-before',
                    'applied-by-default-if-requested',
                    'applied-by-default-if-requested-for-client',
                ],
            ],
            'specific_strict' => [
                1003000,
                [
                    'user.username.read',
                    'user.email_address.read',
                    'defined-but-not-assigned',
                    'disabled-scope',
                    'disabled-scope-for-client',
                    'non-existing',
                ],
                false,
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
            'specific_generic' => [
                1003000,
                [
                    'user.username.read',
                    'user.email_address.read',
                    'defined-but-not-assigned',
                    'disabled-scope',
                    'disabled-scope-for-client',
                    'non-existing',
                ],
                true,
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
                    [ // updated, scope id as column.
                        'scope_id' => 5, // identifier: 'phone'.
                        'applied_by_default' => Oauth2ScopeInterface::APPLIED_BY_DEFAULT_AUTOMATICALLY,
                    ],
                    [ // new, scope id as column.
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
