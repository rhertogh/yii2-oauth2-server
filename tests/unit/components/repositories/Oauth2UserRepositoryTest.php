<?php

namespace Yii2Oauth2ServerTests\unit\components\repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2UserRepositoryInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2PasswordGrantUserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\_helpers\TestUserModelOidc;
use Yii2Oauth2ServerTests\_helpers\TestUserModelPasswordGrant;
use Yii2Oauth2ServerTests\unit\components\repositories\_base\BaseOauth2RepositoryTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\repositories\Oauth2UserRepository
 */
class Oauth2UserRepositoryTest extends BaseOauth2RepositoryTest
{
    /**
     * @return Oauth2UserInterface|string
     */
    protected function getModelInterface()
    {
        return Oauth2UserInterface::class;
    }

    /**
     * @return Oauth2UserInterface|string
     */
    protected function getModelClass()
    {
        return Oauth2Module::getInstance()->identityClass;
    }

    /**
     * @param int|string $userIdentifier
     * @param bool $expectUser
     *
     * @dataProvider getUserEntityByIdentifierProvider
     */
    public function testGetUserEntityByIdentifier($userIdentifier, $expectUser)
    {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'identityClass' => TestUserModelOidc::class,
                ],
            ],
        ]);

        $user = $this->getUserRepository()->getUserEntityByIdentifier($userIdentifier);

        if ($expectUser) {
            $this->assertInstanceOf($this->getModelClass(), $user);
            $this->assertEquals($userIdentifier, $user->getIdentifier());
        } else {
            $this->assertNull($user);
        }
    }

    /**
     * @return array[]
     * @see testGetUserEntityByIdentifier()
     */
    public function getUserEntityByIdentifierProvider()
    {
        return [
            [123, true],
            [124, true],
            [999, false],
        ];
    }

    public function testGetUserEntityByIdentifierWithInvalidFindIdentityClass()
    {
        // phpcs:ignore Generic.Files.LineLength.TooLong -- readability acually better on single line
        $mockTestUserModelClass = get_class(new class extends TestUserModel implements Oauth2PasswordGrantUserInterface {
            public function validatePassword($password)
            {
            }
            public static function findByUsername($username)
            {
            }

            public static function findIdentity($id)
            {
                return new \stdClass();
            }
        });

        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'identityClass' => $mockTestUserModelClass,
                ],
            ],
        ]);

        $this->expectExceptionMessage(
            $mockTestUserModelClass . '::findIdentity() must return an instance of ' . Oauth2UserInterface::class
        );
        $this->getUserRepository()->getUserEntityByIdentifier(123);
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $grantType
     * @param string $clientIdentifier
     * @param string|null $userIdentifier
     * @param string|null $username
     *
     * @dataProvider getUserEntityByUserCredentialsProvider
     */
    public function testGetUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        $clientIdentifier,
        $userIdentifier
    ) {
        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    // TestUserModel with Oauth2PasswordGrantUserInterface.
                    'identityClass' => TestUserModelPasswordGrant::class,
                ],
            ],
        ]);

        $client = static::getClientClass()::findOne(['identifier' => $clientIdentifier]);
        $user = $this->getUserRepository()->getUserEntityByUserCredentials($username, $password, $grantType, $client);

        if ($userIdentifier !== null) {
            $this->assertInstanceOf($this->getModelClass(), $user);
            $this->assertEquals($userIdentifier, $user->getIdentifier());
        } else {
            $this->assertNull($user);
        }
    }

    /**
     * @return array[]
     * @see testGetUserEntityByUserCredentials()
     */
    public function getUserEntityByUserCredentialsProvider()
    {
        // phpcs:disable Generic.Files.LineLength.TooLong -- readability acually better on single line
        return [
            ['test.user', 'password',  Oauth2Module::GRANT_TYPE_IDENTIFIER_PASSWORD, 'test-client-type-password-public-valid', 123],
            ['test.user', 'incorrect', Oauth2Module::GRANT_TYPE_IDENTIFIER_PASSWORD, 'test-client-type-password-public-valid', null],
            ['missing',   'password',  Oauth2Module::GRANT_TYPE_IDENTIFIER_PASSWORD, 'test-client-type-password-public-valid', null],
        ];
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    public function testGetUserEntityByUserCredentialsWithInvalidFindByUsernameClass()
    {
        // phpcs:ignore Generic.Files.LineLength.TooLong -- readability acually better on single line
        $mockTestUserModelClass = get_class(new class extends TestUserModel implements Oauth2PasswordGrantUserInterface {
            public function validatePassword($password)
            {
            }

            public static function findByUsername($username)
            {
                return new \stdClass();
            }
        });

        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'identityClass' => $mockTestUserModelClass,
                ],
            ],
        ]);

        $client = static::getClientClass()::findOne(['identifier' => 'test-client-type-password-public-valid']);
        $this->expectExceptionMessage(
            $mockTestUserModelClass . '::findByUsername() must return an instance of ' . Oauth2UserInterface::class
        );
        $this->getUserRepository()->getUserEntityByUserCredentials(
            'test.user',
            'password',
            Oauth2Module::GRANT_TYPE_IDENTIFIER_PASSWORD,
            $client
        );
    }

    public function testGetUserEntityByUserCredentialsWithoutOauth2PasswordGrantUserInterface()
    {
        // Run with default TestUserModel set as Oauth2UserInterface.
        $client = static::getClientClass()::findOne(['identifier' => 'test-client-type-password-public-valid']);
        $this->expectExceptionMessage(
            'In order to support the `password` grant type, ' . TestUserModel::class
                . ' must implement ' . Oauth2PasswordGrantUserInterface::class
        );
        $this->getUserRepository()->getUserEntityByUserCredentials(
            'test.user',
            'password',
            Oauth2Module::GRANT_TYPE_IDENTIFIER_PASSWORD,
            $client
        );
    }

    public function testGetUserEntityByUserCredentialsWithoutOauth2FindByUsernamePasswordGrantUserInterface()
    {
        // phpcs:ignore Generic.Files.LineLength.TooLong -- readability acually better on single line
        $mockTestUserModelClass = get_class(new class extends TestUserModel implements Oauth2PasswordGrantUserInterface {
            public function validatePassword($password)
            {
            }

            public static function findByUsername($username)
            {
                // returning TestUserModel which doesn't implement Oauth2PasswordGrantUserInterface.
                return new TestUserModel();
            }
        });

        $this->mockConsoleApplication([
            'modules' => [
                'oauth2' => [
                    'identityClass' => $mockTestUserModelClass,
                ],
            ],
        ]);

        $client = static::getClientClass()::findOne(['identifier' => 'test-client-type-password-public-valid']);
        $this->expectExceptionMessage(
            'In order to support the `password` grant type, ' . $mockTestUserModelClass
                . '::findByUsername() must return an instance of ' . Oauth2PasswordGrantUserInterface::class
        );
        $this->getUserRepository()->getUserEntityByUserCredentials(
            'test.user',
            'password',
            Oauth2Module::GRANT_TYPE_IDENTIFIER_PASSWORD,
            $client
        );
    }

    /**
     * @return Oauth2UserRepositoryInterface
     * @throws \yii\base\InvalidConfigException
     */
    protected function getUserRepository()
    {
        return Yii::createObject(Oauth2UserRepositoryInterface::class)
            ->setModule(Oauth2Module::getInstance());
    }
}
