<?php

namespace Yii2Oauth2ServerTests\unit\components\server\grants\traits;

use GuzzleHttp\Psr7\ServerRequest;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use rhertogh\Yii2Oauth2Server\components\server\grants\traits\Oauth2GrantTrait;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use yii\base\InvalidConfigException;
use Yii2Oauth2ServerTests\unit\components\server\grants\_base\BaseOauth2GrantTest;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\server\grants\traits\Oauth2GrantTrait
 * @see BaseOauth2GrantTest for other tests
 */
class Oauth2GrantTraitTest extends TestCase
{
    public function testValidateRedirectUriWithInvalidClient()
    {
        $mock = new class () {
            use Oauth2GrantTrait;
        };

        $client = new class () implements ClientEntityInterface {
            public function getIdentifier()
            {
                return '';
            }
            public function getName()
            {
                return '';
            }
            public function getRedirectUri()
            {
                return '';
            }
            public function isConfidential()
            {
                return '';
            }
        };

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(get_class($client) . ' must implement ' . Oauth2ClientInterface::class);
        $this->callInaccessibleMethod($mock, 'validateRedirectUri', [
            'http://localhost/test',
            $client,
            new ServerRequest('POST', 'https://localhost/test')
        ]);
    }
}
