<?php

namespace Yii2Oauth2ServerTests\unit\components\authorization\client\base;

use rhertogh\Yii2Oauth2Server\components\authorization\client\base\Oauth2BaseClientAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\authorization\client\base\Oauth2BaseClientAuthorizationRequest
 */
class Oauth2BaseClientAuthorizationRequestTest extends DatabaseTestCase
{
    public function testSerialization()
    {
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();
        $authorizeUrl = 'https://localhost/auth_url';
        $grantType = Oauth2Module::GRANT_TYPE_AUTH_CODE;
        $prompts = 'abc';
        $maxAge = 1716921219;
        $requestedScopeIdentifiers = ['scope1', 'scope2', 'scope3'];
        $selectedScopeIdentifiers = ['scope1', 'scope2'];

        // phpcs:disable Generic.Files.LineLength.TooLong -- readability acually better on single line
        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_authorizeUrl', $authorizeUrl);
        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_grantType', $grantType);
        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_prompts', $prompts);
        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_maxAge', $maxAge);
        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_requestedScopeIdentifiers', $requestedScopeIdentifiers);
        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_selectedScopeIdentifiers', $selectedScopeIdentifiers);
        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_userAuthenticatedBeforeRequest', true);
        $this->setInaccessibleProperty($baseClientAuthorizationRequest, '_authenticatedDuringRequest', true);

        $baseClientAuthorizationRequest = unserialize(serialize($baseClientAuthorizationRequest));
        $this->assertInstanceOf(Oauth2BaseClientAuthorizationRequest::class, $baseClientAuthorizationRequest);

        $this->assertEquals($authorizeUrl, $this->getInaccessibleProperty($baseClientAuthorizationRequest, '_authorizeUrl'));
        $this->assertEquals($grantType, $this->getInaccessibleProperty($baseClientAuthorizationRequest, '_grantType'));
        $this->assertEquals($prompts, $this->getInaccessibleProperty($baseClientAuthorizationRequest, '_prompts'));
        $this->assertEquals($maxAge, $this->getInaccessibleProperty($baseClientAuthorizationRequest, '_maxAge'));
        $this->assertEquals($requestedScopeIdentifiers, $this->getInaccessibleProperty($baseClientAuthorizationRequest, '_requestedScopeIdentifiers'));
        $this->assertEquals($selectedScopeIdentifiers, $this->getInaccessibleProperty($baseClientAuthorizationRequest, '_selectedScopeIdentifiers'));
        $this->assertTrue($this->getInaccessibleProperty($baseClientAuthorizationRequest, '_userAuthenticatedBeforeRequest'));
        $this->assertTrue($this->getInaccessibleProperty($baseClientAuthorizationRequest, '_authenticatedDuringRequest'));
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    public function testGetClientWithoutIdentifier()
    {
        $clientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();
        $clientAuthorizationRequest->setModule(Oauth2Module::getInstance());

        $this->expectExceptionMessage('Client identifier must be set.');
        $clientAuthorizationRequest->getClient();
    }

    public function testGetSetAuthorizeUrl()
    {
        $authorizeUrl = 'https://localhost/authorize';
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

        $baseClientAuthorizationRequest->setAuthorizeUrl($authorizeUrl);
        $this->assertEquals($authorizeUrl, $baseClientAuthorizationRequest->getAuthorizeUrl());
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

    public function testGetSetPrompt()
    {
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

        $prompts = ['test'];
        $baseClientAuthorizationRequest->setPrompts($prompts);
        $this->assertEquals($prompts, $baseClientAuthorizationRequest->getPrompts());
    }

    public function testGetSetMaxAge()
    {
        $baseClientAuthorizationRequest = $this->getMockBaseClientAuthorizationRequest();

        $maxAge = 123;
        $baseClientAuthorizationRequest->setMaxAge($maxAge);
        $this->assertEquals($maxAge, $baseClientAuthorizationRequest->getMaxAge());
    }

    protected function getMockBaseClientAuthorizationRequest()
    {
        return $this->getMockForAbstractClass(
            Oauth2BaseClientAuthorizationRequest::class,
        );
    }
}
