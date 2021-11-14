<?php

namespace Yii2Oauth2ServerTests\unit\components\authorization\base;

use rhertogh\Yii2Oauth2Server\components\authorization\base\Oauth2BaseClientAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\authorization\base\Oauth2BaseClientAuthorizationRequest
 */
class Oauth2BaseClientAuthorizationRequestTest extends TestCase
{
    public function testGetSetModule()
    {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

        $baseClientAuthorizationRequest->setModule($module);
        $this->assertEquals($module, $baseClientAuthorizationRequest->getModule());
    }

    public function testGetModuleWithoutItBeingSet()
    {
        $this->mockWebApplication();
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

        $this->expectExceptionMessage('Can not call getModule() before it\'s set.');
        $baseClientAuthorizationRequest->getModule();
    }

    public function testGetSetAuthorizationStatus()
    {
        $authorizationStatus = Oauth2BaseClientAuthorizationRequest::AUTHORIZATION_APPROVED;
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_isCompleted', true);
        $baseClientAuthorizationRequest->setAuthorizationStatus($authorizationStatus);
        $this->assertEquals($authorizationStatus, $baseClientAuthorizationRequest->getAuthorizationStatus());
        $this->assertFalse($baseClientAuthorizationRequest->isCompleted());
    }

    public function testSetInvalidAuthorizationStatus()
    {
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

        $this->expectExceptionMessage('$authorizationStatus must be null or exist in AUTHORIZATION_STATUSES.');
        $baseClientAuthorizationRequest->setAuthorizationStatus('non-existing');
    }

    public function testGetSetClientIdentifier()
    {
        $clientIdentifier = 'abc';
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

        $baseClientAuthorizationRequest->setClientIdentifier($clientIdentifier);
        $this->assertEquals($clientIdentifier, $baseClientAuthorizationRequest->getClientIdentifier());
    }

    public function testGetSetUserIdentity()
    {
        $this->mockWebApplication();
        $user = TestUserModel::findOne(['id' => 123]);
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

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
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();
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

    public function testGetSetAuthorizeUrl()
    {
        $authorizeUrl = 'https://localhost/authorize';
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

        $baseClientAuthorizationRequest->setAuthorizeUrl($authorizeUrl);
        $this->assertEquals($authorizeUrl, $baseClientAuthorizationRequest->getAuthorizeUrl());
    }

    public function testGetSetRedirectUri()
    {
        $redirectUri = 'https://localhost/redirect';
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

        $baseClientAuthorizationRequest->setRedirectUri($redirectUri);
        $this->assertEquals($redirectUri, $baseClientAuthorizationRequest->getRedirectUri());
    }

    public function testGetSetRequestedScopeIdentifiers()
    {
        $requestedScopeIdentifiers = ['scope1', 'scope2'];
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_isCompleted', true);
        $baseClientAuthorizationRequest->setRequestedScopeIdentifiers($requestedScopeIdentifiers);
        $this->assertEquals(
            $requestedScopeIdentifiers,
            $baseClientAuthorizationRequest->getRequestedScopeIdentifiers()
        );
        $this->assertFalse($baseClientAuthorizationRequest->isCompleted());
    }

    public function testGetSetSelectedScopeIdentifiers()
    {
        $selectedScopeIdentifiers = ['scope1', 'scope2'];
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_isCompleted', true);
        $baseClientAuthorizationRequest->setSelectedScopeIdentifiers($selectedScopeIdentifiers);
        $this->assertEquals($selectedScopeIdentifiers, $baseClientAuthorizationRequest->getSelectedScopeIdentifiers());
        $this->assertFalse($baseClientAuthorizationRequest->isCompleted());
    }

    public function testGetSetGrantType()
    {
        $grantType = Oauth2Module::GRANT_TYPE_IDENTIFIER_AUTH_CODE;
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

        $baseClientAuthorizationRequest->setGrantType($grantType);
        $this->assertEquals($grantType, $baseClientAuthorizationRequest->getGrantType());
    }

    public function testIsApproved()
    {
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

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
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

        $this->assertFalse($baseClientAuthorizationRequest->isCompleted());
        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_isCompleted', true);
        $this->assertTrue($baseClientAuthorizationRequest->isCompleted());
        $baseClientAuthorizationRequest->setAuthorizationStatus(null);
        $this->assertFalse($baseClientAuthorizationRequest->isCompleted());
    }

    protected function getMockBaseClientAuthorizationRequest()
    {
        return $this->getMockForAbstractClass(
            Oauth2BaseClientAuthorizationRequest::class,
        );
    }
}
