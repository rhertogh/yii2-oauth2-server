<?php

namespace Yii2Oauth2ServerTests\unit\models\_traits;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AccessTokenInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2AuthCodeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use Yii;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BaseModelTestTrait;
use Yii2Oauth2ServerTests\unit\models\_traits\_base\Oauth2BasePhpUnitTestCaseTrait;

trait Oauth2GetSetClientTestTrait
{
    use Oauth2BaseModelTestTrait;
    use Oauth2BasePhpUnitTestCaseTrait;

    public function testGetSetClient()
    {
        $mocks = $this->getMockModelAndClient(); // Workaround for https://youtrack.jetbrains.com/issue/WI-61907.
        $model = $mocks['model'];
        $clientId = $mocks['clientId'];
        $client = $mocks['client'];

        $this->assertNull($model->getClient());
        $model->setClient($client);
        $this->assertEquals($client, $model->getClient());
        $this->assertEquals($clientId, $model->getAttribute('client_id'));
        $this->assertEquals($clientId, $model->client_id);
    }

    /**
     * @depends testGetSetClient
     */
    public function testSetAttributeClientId()
    {
        $mocks = $this->getMockModelAndClient(); // Workaround for https://youtrack.jetbrains.com/issue/WI-61907.
        $model = $mocks['model'];
        $client = $mocks['client'];

        $model->setClient($client);
        $model->setAttribute('client_id', 456);
        $this->assertNull($model->getClient());
    }

    /**
     * @depends testGetSetClient
     */
    public function testSetClientId()
    {
        $mocks = $this->getMockModelAndClient(); // Workaround for https://youtrack.jetbrains.com/issue/WI-61907.
        $model = $mocks['model'];
        $client = $mocks['client'];

        $model->setClient($client);
        $model->client_id = 456;
        $this->assertNull($model->getClient());
    }

    public function testSetInvalidClient()
    {
        /** @var Oauth2AccessTokenInterface|Oauth2AuthCodeInterface $model */
        $model = $this->getMockModel();

        $mockClient = new class implements ClientEntityInterface {
            use ClientTrait;

            public function getIdentifier()
            {
                return null;
            }
        };

        $this->expectExceptionMessage(get_class($mockClient) . ' must implement ' . Oauth2ClientInterface::class);
        $model->setClient($mockClient);
    }

    /**
     * // phpcs:ignore Generic.Files.LineLength.TooLong -- single line is required for PhpStorm
     * @return array{model: Oauth2AccessTokenInterface|Oauth2AuthCodeInterface, clientId: int, client: Oauth2ClientInterface}
     * @throws \yii\base\InvalidConfigException
     */
    protected function getMockModelAndClient()
    {
        $model = $this->getMockModel();

        $clientId = 101;

        /** @var Oauth2ClientInterface $client */
        $client = Yii::createObject([
            'class' => Oauth2ClientInterface::class,
            'id' => $clientId,
        ]);

        return [
            'model' => $model,
            'clientId' => $clientId,
            'client' => $client,
        ];
    }
}
