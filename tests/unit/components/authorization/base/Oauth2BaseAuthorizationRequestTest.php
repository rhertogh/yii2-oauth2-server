<?php

namespace Yii2Oauth2ServerTests\unit\components\authorization\base;

use rhertogh\Yii2Oauth2Server\components\authorization\base\Oauth2BaseAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\components\authorization\client\base\Oauth2BaseClientAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\base\Oauth2BaseAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2UserInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\authorization\base\Oauth2BaseAuthorizationRequest
 */
class Oauth2BaseAuthorizationRequestTest extends DatabaseTestCase
{
    public function testSerialization()
    {
        $baseAuthorizationRequest = $this->getMockBaseAuthorizationRequest();
        $requestId = 'req-id';
        $clientIdentifier = 'client-id';
        $userIdentifier = 123;
        $redirectUri = 'https://localhost/redirect_url';
        $authorizationStatus = Oauth2BaseAuthorizationRequestInterface::AUTHORIZATION_APPROVED;
        $isCompleted = true;

        // phpcs:disable Generic.Files.LineLength.TooLong -- readability acually better on single line
        $this->setInaccessibleProperty($baseAuthorizationRequest, '_requestId', $requestId);
        $this->setInaccessibleProperty($baseAuthorizationRequest, '_clientIdentifier', $clientIdentifier);
        $this->setInaccessibleProperty($baseAuthorizationRequest, '_userIdentifier', $userIdentifier);
        $this->setInaccessibleProperty($baseAuthorizationRequest, '_redirectUri', $redirectUri);
        $this->setInaccessibleProperty($baseAuthorizationRequest, '_authorizationStatus', $authorizationStatus);
        $this->setInaccessibleProperty($baseAuthorizationRequest, '_isCompleted', $isCompleted);

        $baseAuthorizationRequest = unserialize(serialize($baseAuthorizationRequest));
        $this->assertInstanceOf(Oauth2BaseAuthorizationRequest::class, $baseAuthorizationRequest);

        $this->assertEquals($requestId, $this->getInaccessibleProperty($baseAuthorizationRequest, '_requestId'));
        $this->assertEquals($clientIdentifier, $this->getInaccessibleProperty($baseAuthorizationRequest, '_clientIdentifier'));
        $this->assertEquals($userIdentifier, $this->getInaccessibleProperty($baseAuthorizationRequest, '_userIdentifier'));
        $this->assertEquals($redirectUri, $this->getInaccessibleProperty($baseAuthorizationRequest, '_redirectUri'));
        $this->assertEquals($authorizationStatus, $this->getInaccessibleProperty($baseAuthorizationRequest, '_authorizationStatus'));
        $this->assertEquals($isCompleted, $this->getInaccessibleProperty($baseAuthorizationRequest, '_isCompleted'));
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    public function testGetSetModule()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $baseClientAuthorizationRequest = $this->getMockBaseAuthorizationRequest();

        $baseClientAuthorizationRequest->setModule($module);
        $this->assertEquals($module, $baseClientAuthorizationRequest->getModule());
    }

    public function testGetModuleWithoutItBeingSet()
    {
        $this->mockWebApplication();
        $baseClientAuthorizationRequest = $this->getMockBaseAuthorizationRequest();

        $this->expectExceptionMessage('Can not call getModule() before it\'s set.');
        $baseClientAuthorizationRequest->getModule();
    }

    public function testGetSetAuthorizationStatus()
    {
        $authorizationStatus = Oauth2BaseClientAuthorizationRequest::AUTHORIZATION_APPROVED;
        $baseClientAuthorizationRequest = $this->getMockBaseAuthorizationRequest();

        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_isCompleted', true);
        $baseClientAuthorizationRequest->setAuthorizationStatus($authorizationStatus);
        $this->assertEquals($authorizationStatus, $baseClientAuthorizationRequest->getAuthorizationStatus());
        $this->assertFalse($baseClientAuthorizationRequest->isCompleted());
    }

    public function testSetInvalidAuthorizationStatus()
    {
        $baseClientAuthorizationRequest = $this->getMockBaseAuthorizationRequest();

        $this->expectExceptionMessage('$authorizationStatus must be null or exist in the return value of `getPossibleAuthorizationStatuses()`.');
        $baseClientAuthorizationRequest->setAuthorizationStatus('non-existing');
    }

    public function testGetSetClientIdentifier()
    {
        $clientIdentifier = 'abc';
        $baseClientAuthorizationRequest = $this->getMockBaseAuthorizationRequest();

        $baseClientAuthorizationRequest->setClient(new Oauth2Client(['identifier' => strrev($clientIdentifier)]));
        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_isCompleted', true);

        $baseClientAuthorizationRequest->setClientIdentifier($clientIdentifier);
        $this->assertEquals($clientIdentifier, $baseClientAuthorizationRequest->getClientIdentifier());

        $this->assertnull($this->getInaccessibleProperty($baseClientAuthorizationRequest, '_client'));
        $this->assertfalse($this->getInaccessibleProperty($baseClientAuthorizationRequest, '_isCompleted'));
    }

    public function testGetSetUserIdentity()
    {
        $this->mockWebApplication();
        $user = TestUserModel::findOne(['id' => 123]);
        $baseClientAuthorizationRequest = $this->getMockBaseAuthorizationRequest();

        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_isCompleted', true);
        $baseClientAuthorizationRequest->setUserIdentity($user);
        $this->assertEquals($user, $baseClientAuthorizationRequest->getUserIdentity());
        $this->assertEquals(
            $user->getId(),
            $this->getInaccessibleProperty($baseClientAuthorizationRequest, '_userIdentifier')
        );
        $this->assertFalse($baseClientAuthorizationRequest->isCompleted());

        $this->assertEquals(
            123,
            $this->callInaccessibleMethod($baseClientAuthorizationRequest, 'getUserIdentifier')
        );
    }

    public function testSetUserIdentifier()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $user = TestUserModel::findOne(['id' => 123]);
        $baseClientAuthorizationRequest = $this->getMockBaseAuthorizationRequest();
        $baseClientAuthorizationRequest->setModule($module);
        $baseClientAuthorizationRequest->setUserIdentity($user);

        $this->assertEquals(
            $user,
            $this->getInaccessibleProperty($baseClientAuthorizationRequest, '_userIdentity')
        );
        $this->callInaccessibleMethod($baseClientAuthorizationRequest, 'setUserIdentifier', [124]);
        $this->assertNull(
            $this->getInaccessibleProperty($baseClientAuthorizationRequest, '_userIdentity')
        );
        $this->assertInstanceOf(
            Oauth2UserInterface::class,
            $baseClientAuthorizationRequest->getUserIdentity()
        );
        $this->assertEquals(124, $baseClientAuthorizationRequest->getUserIdentity()->getIdentifier());
    }

    public function testGetSetRedirectUri()
    {
        $redirectUri = 'https://localhost/redirect';
        $baseClientAuthorizationRequest = $this->getMockBaseAuthorizationRequest();

        $baseClientAuthorizationRequest->setRedirectUri($redirectUri);
        $this->assertEquals($redirectUri, $baseClientAuthorizationRequest->getRedirectUri());
    }

    public function testIsApproved()
    {
        $baseClientAuthorizationRequest = $this->getMockBaseAuthorizationRequest();

        $this->assertFalse($baseClientAuthorizationRequest->isApproved());
        $baseClientAuthorizationRequest->setAuthorizationStatus(
            Oauth2BaseClientAuthorizationRequest::AUTHORIZATION_APPROVED
        );
        $this->assertTrue($baseClientAuthorizationRequest->isApproved());
        $baseClientAuthorizationRequest->setAuthorizationStatus(
            Oauth2BaseClientAuthorizationRequest::AUTHORIZATION_DENIED
        );
        $this->assertFalse($baseClientAuthorizationRequest->isApproved());
        $baseClientAuthorizationRequest->setAuthorizationStatus(null);
        $this->assertFalse($baseClientAuthorizationRequest->isApproved());
    }

    public function testIsCompleted()
    {
        $baseClientAuthorizationRequest = $this->getMockBaseAuthorizationRequest();

        $this->assertFalse($baseClientAuthorizationRequest->isCompleted());
        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_isCompleted', true);
        $this->assertTrue($baseClientAuthorizationRequest->isCompleted());
        $baseClientAuthorizationRequest->setAuthorizationStatus(null);
        $this->assertFalse($baseClientAuthorizationRequest->isCompleted());
    }

    protected function getMockBaseAuthorizationRequest()
    {
        return $this->getMockForAbstractClass(
            Oauth2BaseAuthorizationRequest::class,
        );
    }
}
