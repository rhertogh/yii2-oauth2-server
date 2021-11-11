<?php
namespace Yii2Oauth2ServerTests\unit\components\repositories;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\repositories\Oauth2ClientRepositoryInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use Yii2Oauth2ServerTests\unit\components\repositories\_base\BaseOauth2RepositoryTest;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\repositories\Oauth2ClientRepository
 *
 * @method Oauth2ClientInterface|string getModelClass()
 */
class Oauth2ClientRepositoryTest extends BaseOauth2RepositoryTest
{
    /**
     * @return Oauth2ClientInterface|string
     */
    protected function getModelInterface()
    {
        return Oauth2ClientInterface::class;
    }

    /**
     * @param string $identifier
     * @param string|null $identifier
     *
     * @dataProvider getClientEntityProvider
     */
    public function testGetClientEntity($identifier, $class)
    {
        $client = $this->getClientRepository()->getClientEntity($identifier);

        if ($class !== null) {
            $this->assertInstanceOf($class, $client);
        } else {
            $this->assertNull($client);
        }

    }

    /**
     * @return array[]
     * @see testGetClientEntity()
     */
    public function getClientEntityProvider()
    {
        return [
            ['test-client-type-auth-code-valid', $this->getModelInterface()],
            ['does-not-exist', null],
        ];
    }

    /**
     * @param string $identifier
     * @param string $secret
     * @param string $grantType
     * @param bool $expectedValid
     *
     * @dataProvider validateClientProvider
     */
    public function testValidateClient($identifier, $secret, $grantType, $expectedValid)
    {
        $valid = $this->getClientRepository()->validateClient($identifier, $secret, $grantType);

        $this->assertEquals($expectedValid, $valid);
    }

    /**
     * @return array[]
     * @see testValidateClient()
     */
    public function validateClientProvider()
    {
        return [
            ['test-client-type-auth-code-valid',            'secret',       'authorization_code', true],
            ['test-client-does-not-exist',                  'secret',       'authorization_code', false],
            ['test-client-type-auth-code-disabled',         'secret',       'authorization_code', false],
            ['test-client-type-client-credentials-valid',   'secret',       'authorization_code', false],
            ['test-client-type-client-credentials-valid',   'secret',       'client_credentials', true],
            ['test-client-type-auth-code-valid',            'wrong-secret', 'authorization_code', false],
            ['test-client-type-password-public-valid',      null,           'password',           true],
        ];
    }

    /**
     * @return Oauth2ClientRepositoryInterface
     * @throws \yii\base\InvalidConfigException
     */
    protected function getClientRepository()
    {
        return Yii::createObject([
            'class' => Oauth2ClientRepositoryInterface::class,
            'module' => Oauth2Module::getInstance(),
        ]);
    }
}
