<?php

namespace Yii2Oauth2ServerTests\unit\components\authorization\client;

use rhertogh\Yii2Oauth2Server\components\authorization\client\Oauth2ClientAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\components\authorization\client\Oauth2ClientScopeAuthorizationRequest;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\client\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\models\Oauth2Scope;
use rhertogh\Yii2Oauth2Server\models\Oauth2UserClient;
use rhertogh\Yii2Oauth2Server\models\Oauth2UserClientScope;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii2Oauth2ServerTests\_helpers\TestUserModel;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\authorization\client\Oauth2ClientAuthorizationRequest
 */
class Oauth2ClientAuthorizationRequestTest extends DatabaseTestCase
{
    public function testInitRandomRequestId()
    {
        $this->mockWebApplication();
        $requests = [];
        for ($i = 0; $i < 100; $i++) {
            $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest();
            $requestId = $clientAuthorizationRequest->getRequestId();
            $this->assertGreaterThanOrEqual(64, strlen($requestId));
            $this->assertArrayNotHasKey($requestId, $requests);
            $requests[$requestId] = true;
        }
    }

    public function testRules()
    {
        $this->assertIsArray($this->getMockClientAuthorizationRequest()->rules());
    }

    public function testGetRequestId()
    {
        $requestId = '123RequestId';
        $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest();
        $this->setInaccessibleProperty($clientAuthorizationRequest, '_requestId', $requestId);

        $this->assertEquals($requestId, $clientAuthorizationRequest->getRequestId());
    }

    public function testGetSetState()
    {
        $state = '123State';
        $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest();

        $this->assertNull($clientAuthorizationRequest->getState());
        $this->assertInstanceOf(
            Oauth2ClientAuthorizationRequestInterface::class,
            $clientAuthorizationRequest->setState($state)
        );
        $this->assertEquals($state, $clientAuthorizationRequest->getState());
    }

    public function testGetSetClientAndSetClientIdentifier()
    {
        $this->mockWebApplication();

        $customClientIdentifier = 'test-custom-client';
        $customClient = new Oauth2Client([
            'identifier' => $customClientIdentifier,
        ]);

        $clientId1 = 1003000;
        $clientIdentifier1 = 'test-client-type-auth-code-valid';

        $clientId2 = 1003001;
        $clientIdentifier2 = 'test-client-type-client-credentials-valid';

        $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest();
        $clientAuthorizationRequest->setModule(Oauth2Module::getInstance());

        // phpcs:disable Generic.Files.LineLength.TooLong -- readability actually better on single line
        $scopeAuthorizationRequests = [new Oauth2ClientScopeAuthorizationRequest()];
        $scopesAppliedByDefaultWithoutConfirm = [new Oauth2Scope()];
        $this->setInaccessibleProperty($clientAuthorizationRequest, '_scopeAuthorizationRequests', $scopeAuthorizationRequests);
        $this->setInaccessibleProperty($clientAuthorizationRequest, '_scopesAppliedByDefaultWithoutConfirm', $scopesAppliedByDefaultWithoutConfirm);
        $this->assertNotNull($this->getInaccessibleProperty($clientAuthorizationRequest, '_scopeAuthorizationRequests'));
        $this->assertNotNull($this->getInaccessibleProperty($clientAuthorizationRequest, '_scopesAppliedByDefaultWithoutConfirm'));

        $clientAuthorizationRequest->setClientIdentifier($clientIdentifier1);
        $this->assertNull($this->getInaccessibleProperty($clientAuthorizationRequest, '_client'));
        $this->assertNull($this->getInaccessibleProperty($clientAuthorizationRequest, '_scopeAuthorizationRequests'));
        $this->assertNull($this->getInaccessibleProperty($clientAuthorizationRequest, '_scopesAppliedByDefaultWithoutConfirm'));
        $this->assertEquals($clientId1, $clientAuthorizationRequest->getClient()->getPrimaryKey());
        $this->assertNotNull($this->getInaccessibleProperty($clientAuthorizationRequest, '_client'));

        $clientAuthorizationRequest->setClientIdentifier($clientIdentifier2);
        $this->assertNull($this->getInaccessibleProperty($clientAuthorizationRequest, '_client'));
        $this->assertEquals($clientId2, $clientAuthorizationRequest->getClient()->getPrimaryKey());

        $clientAuthorizationRequest->setClient($customClient);
        $this->assertEquals($customClient, $clientAuthorizationRequest->getClient());
        $this->assertEquals($customClientIdentifier, $this->getInaccessibleProperty($clientAuthorizationRequest, '_clientIdentifier'));
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    public function testGetSetUser()
    {
        $this->mockWebApplication();

        $user = TestUserModel::findOne(123);

        $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest();
        $clientAuthorizationRequest->setModule(Oauth2Module::getInstance());

        // phpcs:disable Generic.Files.LineLength.TooLong -- readability actually better on single line
        $scopeAuthorizationRequests = [new Oauth2ClientScopeAuthorizationRequest()];
        $scopesAppliedByDefaultWithoutConfirm = [new Oauth2Scope()];
        $this->setInaccessibleProperty($clientAuthorizationRequest, '_scopeAuthorizationRequests', $scopeAuthorizationRequests);
        $this->setInaccessibleProperty($clientAuthorizationRequest, '_scopesAppliedByDefaultWithoutConfirm', $scopesAppliedByDefaultWithoutConfirm);
        $this->assertNotNull($this->getInaccessibleProperty($clientAuthorizationRequest, '_scopeAuthorizationRequests'));
        $this->assertNotNull($this->getInaccessibleProperty($clientAuthorizationRequest, '_scopesAppliedByDefaultWithoutConfirm'));

        $clientAuthorizationRequest->setUserIdentity($user);
        $this->assertNull($this->getInaccessibleProperty($clientAuthorizationRequest, '_scopeAuthorizationRequests'));
        $this->assertNull($this->getInaccessibleProperty($clientAuthorizationRequest, '_scopesAppliedByDefaultWithoutConfirm'));
        $this->assertEquals($user, $clientAuthorizationRequest->getUserIdentity());
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    public function testSetWasUserAuthenticatedBeforeRequest()
    {
        $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest();

        $this->assertFalse($clientAuthorizationRequest->wasUserAuthenticatedBeforeRequest());
        $this->assertInstanceOf(
            Oauth2ClientAuthorizationRequestInterface::class,
            $clientAuthorizationRequest->setUserAuthenticatedBeforeRequest(true)
        );
        $this->assertTrue($clientAuthorizationRequest->wasUserAuthenticatedBeforeRequest());
    }

    public function testSetWasUserAuthenticatedDuringRequest()
    {
        $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest();

        $this->assertFalse($clientAuthorizationRequest->wasUserAthenticatedDuringRequest());
        $this->assertInstanceOf(
            Oauth2ClientAuthorizationRequestInterface::class,
            $clientAuthorizationRequest->setUserAuthenticatedDuringRequest(true)
        );
        $this->assertTrue($clientAuthorizationRequest->wasUserAthenticatedDuringRequest());
    }

    public function testSetRequestedScopeIdentifiers()
    {
        $this->mockWebApplication();

        $requestedScopeIdentifiers = ['scope1', 'scope2'];
        $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest();

        // phpcs:disable Generic.Files.LineLength.TooLong -- readability actually better on single line
        $scopeAuthorizationRequests = [new Oauth2ClientScopeAuthorizationRequest()];
        $scopesAppliedByDefaultWithoutConfirm = [new Oauth2Scope()];
        $this->setInaccessibleProperty($clientAuthorizationRequest, '_scopeAuthorizationRequests', $scopeAuthorizationRequests);
        $this->setInaccessibleProperty($clientAuthorizationRequest, '_scopesAppliedByDefaultWithoutConfirm', $scopesAppliedByDefaultWithoutConfirm);
        $this->assertNotNull($this->getInaccessibleProperty($clientAuthorizationRequest, '_scopeAuthorizationRequests'));
        $this->assertNotNull($this->getInaccessibleProperty($clientAuthorizationRequest, '_scopesAppliedByDefaultWithoutConfirm'));

        $clientAuthorizationRequest->setRequestedScopeIdentifiers($requestedScopeIdentifiers);
        $this->assertEquals($requestedScopeIdentifiers, $clientAuthorizationRequest->getRequestedScopeIdentifiers());
        $this->assertNull($this->getInaccessibleProperty($clientAuthorizationRequest, '_scopeAuthorizationRequests'));
        $this->assertNull($this->getInaccessibleProperty($clientAuthorizationRequest, '_scopesAppliedByDefaultWithoutConfirm'));
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    public function testIsClientIdentifiable()
    {
        $this->mockWebApplication();

        $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest();
        $clientAuthorizationRequest->setModule(Oauth2Module::getInstance());

        $clientAuthorizationRequest->setClientIdentifier('test-client-type-auth-code-valid');
        $this->assertTrue($clientAuthorizationRequest->isClientIdentifiable());
        $clientAuthorizationRequest->setClientIdentifier('test-client-type-password-public-valid');
        $this->assertFalse($clientAuthorizationRequest->isClientIdentifiable());
        $clientAuthorizationRequest->setRedirectUri('https://localhost/redirect_uri');
        $this->assertTrue($clientAuthorizationRequest->isClientIdentifiable());
    }

    public function testisAuthorizationNeeded()
    {
        $this->mockWebApplication();

        $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest();
        $clientAuthorizationRequest->setModule(Oauth2Module::getInstance());

        $user123 = TestUserModel::findOne(123);
        $user124 = TestUserModel::findOne(124);

        // Pre-approved client for user.
        $clientAuthorizationRequest->setClientIdentifier('test-client-type-password-public-valid');
        $clientAuthorizationRequest->setUserIdentity($user124);
        $clientAuthorizationRequest->setRedirectUri('https://localhost/redirect_uri');  // Note the https protocol.
        $this->assertFalse($clientAuthorizationRequest->isAuthorizationNeeded());

        // Client not yet authorized by user.
        $clientAuthorizationRequest->setUserIdentity($user123);
        $this->assertTrue($clientAuthorizationRequest->isAuthorizationNeeded());

        // [restore config to Pre-approved client for user].
        $clientAuthorizationRequest->setUserIdentity($user124);
        $this->assertFalse($clientAuthorizationRequest->isAuthorizationNeeded());

        // Unidentifiable Client.
        $clientAuthorizationRequest->setRedirectUri('http://localhost/redirect_uri'); // Note the http protocol.
        $this->assertTrue($clientAuthorizationRequest->isAuthorizationNeeded());

        // [restore config to Pre-approved client for user].
        $clientAuthorizationRequest->setRedirectUri('https://localhost/redirect_uri'); // Note the https protocol.
        $this->assertFalse($clientAuthorizationRequest->isAuthorizationNeeded()); // Note the https protocol.

        // Additional scope for pre-approved client for user.
        $clientAuthorizationRequest->setRequestedScopeIdentifiers(['user.enabled.read']);
        $this->assertTrue($clientAuthorizationRequest->isAuthorizationNeeded());

        // [restore config to Pre-approved client for user].
        $clientAuthorizationRequest->setRequestedScopeIdentifiers([]);
        $this->assertFalse($clientAuthorizationRequest->isAuthorizationNeeded());

        // Skip Authorization If Scope Is Allowed (client authorized).
        $clientAuthorizationRequest->getClient()->skip_authorization_if_scope_is_allowed = true;
        $this->assertFalse($clientAuthorizationRequest->isAuthorizationNeeded());

        // Use client with `skip_authorization_if_scope_is_allowed` enabled.
        $clientAuthorizationRequest->setClientIdentifier(
            'test-client-type-auth-code-open-id-connect-skip-authorization'
        );
        // Client not yet authorized by user.
        $clientAuthorizationRequest->setUserIdentity($user123);
        $clientAuthorizationRequest->setRequestedScopeIdentifiers([]);
        $this->assertFalse($clientAuthorizationRequest->isAuthorizationNeeded());
        // Additional scope for not yet authorized by user.
        $clientAuthorizationRequest->setRequestedScopeIdentifiers(['offline_access']);
        $this->assertTrue($clientAuthorizationRequest->isAuthorizationNeeded());
        // Pre-approved client for user.
        $clientAuthorizationRequest->setUserIdentity($user124);
        $clientAuthorizationRequest->setRequestedScopeIdentifiers([]);
        $this->assertFalse($clientAuthorizationRequest->isAuthorizationNeeded());
        // Additional scope for pre-approved client for user.
        $clientAuthorizationRequest->setRequestedScopeIdentifiers(['offline_access']);
        $this->assertTrue($clientAuthorizationRequest->isAuthorizationNeeded());
    }

    public function testGetApprovalPendingScopes()
    {
        $this->mockWebApplication();

        $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest();
        $clientAuthorizationRequest->setModule(Oauth2Module::getInstance());

        $expectedScopes = $requestedScopes = [
            'user.id.read',
            'user.username.read',
            'user.email_address.read',
            'user.enabled.read',
            'not-required',
            'not-required-has-been-rejected-before',
        ];

        $requestedScopes[] = 'applied-automatically-by-default-for-client';
        $requestedScopes[] = 'applied-by-default-if-requested';
        $requestedScopes[] = 'applied-by-default-if-requested-for-client';

        $user123 = TestUserModel::findOne(123);
        $user124 = TestUserModel::findOne(124);

        $clientAuthorizationRequest->setClientIdentifier('test-client-type-password-public-valid');
        $clientAuthorizationRequest->setRequestedScopeIdentifiers($requestedScopes);

        // User with no pre-approved scopes.
        $clientAuthorizationRequest->setUserIdentity($user123);
        $approvalPendingScopes = $clientAuthorizationRequest->getApprovalPendingScopes();
        $this->assertEquals($expectedScopes, array_keys($approvalPendingScopes));
        $this->assertEquals([], $clientAuthorizationRequest->getPreviouslyApprovedScopes());

        // User with pre-approved scopes.
        $clientAuthorizationRequest->setUserIdentity($user124);
        $approvalPendingScopes = $clientAuthorizationRequest->getApprovalPendingScopes();
        $previouslyApprovedScopes = $clientAuthorizationRequest->getPreviouslyApprovedScopes();
        $this->assertEquals(
            [
                'user.enabled.read',
                'not-required',
                'not-required-has-been-rejected-before',
            ],
            array_keys($approvalPendingScopes)
        );
        $this->assertEquals(
            [
                'user.id.read',
                'user.username.read',
                'user.email_address.read',
            ],
            array_keys($previouslyApprovedScopes)
        );

        $this->assertTrue($approvalPendingScopes['user.enabled.read']->getIsRequired());
        $this->assertFalse($approvalPendingScopes['not-required']->getIsRequired());
        $this->assertFalse($approvalPendingScopes['not-required']->getHasBeenRejectedBefore());
        $this->assertTrue($approvalPendingScopes['not-required-has-been-rejected-before']->getHasBeenRejectedBefore());
    }

    /**
     * @dataProvider getScopesAppliedByDefaultWithoutConfirmProvider
     */
    public function testGetScopesAppliedByDefaultWithoutConfirm($requestedScopes, $expectedScopes)
    {
        $this->mockWebApplication();

        $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest()
            ->setModule(Oauth2Module::getInstance())
            ->setClient(Oauth2Client::findByIdentifier('test-client-type-auth-code-valid'))
            ->setUserIdentity(TestUserModel::findIdentity(123))
            ->setRequestedScopeIdentifiers($requestedScopes);

        $scopes = array_keys($clientAuthorizationRequest->getScopesAppliedByDefaultWithoutConfirm());
        sort($scopes);
        $this->assertEquals($expectedScopes, $scopes);
    }

    public function getScopesAppliedByDefaultWithoutConfirmProvider()
    {
        return [
            'no scopes requested' => [
                [],
                [
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                ],
            ],

            'applied-by-default-if-requested on scope' => [
                [
                    'applied-by-default-if-requested',
                ],
                [
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                    'applied-by-default-if-requested',
                ],
            ],

            'applied-by-default-if-requested on client_scope' => [
                [
                    'applied-by-default-if-requested-for-client',
                ],
                [
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                    'applied-by-default-if-requested-for-client',
                ],
            ],

            'applied-by-default-if-requested on both client and client_scope' => [
                [
                    'applied-by-default-if-requested',
                    'applied-by-default-if-requested-for-client',
                ],
                [
                    'applied-automatically-by-default',
                    'applied-automatically-by-default-for-client',
                    'applied-by-default-if-requested',
                    'applied-by-default-if-requested-for-client',
                ],
            ],
        ];
    }

    public function testProcessAuthorizationApprove()
    {
        $this->mockWebApplication();

        $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest();
        $clientAuthorizationRequest->setModule(Oauth2Module::getInstance());

        $selectedScopes = $requestedScopes = [
            'user.id.read',
            'user.username.read',
            'user.email_address.read',
            'user.enabled.read',
        ];

        $requestedScopes[] = 'not-required';
        $requestedScopes[] = 'applied-automatically-by-default-for-client';

        $user = TestUserModel::findOne(123);

        $clientAuthorizationRequest->setClientIdentifier('test-client-type-auth-code-valid');
        $clientAuthorizationRequest->setRequestedScopeIdentifiers($requestedScopes);
        $clientAuthorizationRequest->setUserIdentity($user);
        $clientAuthorizationRequest->setSelectedScopeIdentifiers($selectedScopes);
        $clientAuthorizationRequest->setAuthorizationStatus(
            Oauth2ClientAuthorizationRequest::AUTHORIZATION_APPROVED
        );

        $clientAuthorizationRequest->processAuthorization();

        $this->assertTrue($clientAuthorizationRequest->isCompleted());
        $this->assertTrue(
            Oauth2UserClient::find()
                ->andWhere([
                    'user_id' => 123,
                    'client_id' => 1003000,
                    'enabled' => 1,
                ])
                ->exists()
        );

        $this->assertEquals(
            4,
            Oauth2UserClientScope::find()
                ->alias('user_client_scope')
                ->innerJoinWith('scope scope', false)
                ->andWhere([
                    'scope.identifier' => $selectedScopes,
                    'user_client_scope.user_id' => 123,
                    'user_client_scope.client_id' => 1003000,
                    'user_client_scope.enabled' => 1,
                ])
                ->count()
        );

        $this->assertEquals(
            4,
            Oauth2UserClientScope::find()
                ->alias('user_client_scope')
                ->innerJoinWith('scope scope', false)
                ->andWhere([
                    'scope.identifier' => [
                        'not-required',
                        'applied-by-default-for-client',
                        'applied-by-default-by-client-not-required-for-client',
                        'applied-by-default',
                    ],
                    'user_client_scope.user_id' => 123,
                    'user_client_scope.client_id' => 1003000,
                    'user_client_scope.enabled' => 0,
                ])
                ->count()
        );
    }

    public function testProcessAuthorizationDenied()
    {
        $this->mockWebApplication();

        $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest();
        $clientAuthorizationRequest->setModule(Oauth2Module::getInstance());

        $user = TestUserModel::findOne(124);

        $clientAuthorizationRequest->setClientIdentifier('test-client-type-auth-code-valid');
        $clientAuthorizationRequest->setUserIdentity($user);
        $clientAuthorizationRequest->setAuthorizationStatus(
            Oauth2ClientAuthorizationRequest::AUTHORIZATION_DENIED
        );

        $this->assertTrue(
            Oauth2UserClient::find()
                ->andWhere([
                    'user_id' => 124,
                    'client_id' => 1003000,
                    'enabled' => 1,
                ])
                ->exists()
        );

        $clientAuthorizationRequest->processAuthorization();

        $this->assertTrue($clientAuthorizationRequest->isCompleted());
        $this->assertTrue(
            Oauth2UserClient::find()
                ->andWhere([
                    'user_id' => 124,
                    'client_id' => 1003000,
                    'enabled' => 0,
                ])
                ->exists()
        );
    }

    public function testProcessAuthorizationWithoutStatus()
    {
        $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest();

        $this->expectExceptionMessage('Unable to process authorization without authorization status.');
        $clientAuthorizationRequest->processAuthorization();
    }

    public function testGetAuthorizationRequestCompletedUrl()
    {
        $this->mockWebApplication();

        $clientAuthorizationRequest = $this->getMockClientAuthorizationRequest();
        $clientAuthorizationRequest->setAuthorizeUrl('https://localhost/authorize?test=abc');

        $this->setInaccessibleProperty($clientAuthorizationRequest, '_requestId', 123);

        $this->assertEquals(
            'https://localhost/authorize?test=abc&clientAuthorizationRequestId=123',
            $clientAuthorizationRequest->getAuthorizationRequestUrl()
        );
    }

    protected function getMockClientAuthorizationRequest()
    {
        return new Oauth2ClientAuthorizationRequest();
    }
}
