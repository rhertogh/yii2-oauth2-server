<?php

namespace Yii2Oauth2ServerTests\unit\components\openidconnect\scopes;

use rhertogh\Yii2Oauth2Server\components\openidconnect\scopes\Oauth2OidcScope;
use rhertogh\Yii2Oauth2Server\components\openidconnect\scopes\Oauth2OidcScopeCollection;
use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcScopeInterface;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\components\openidconnect\scopes\Oauth2OidcScopeCollection
 */
class Oauth2OidcScopeCollectionTest extends TestCase
{
    public function testGetSetAddClearOidcScopes()
    {
        $this->mockConsoleApplication();
        $collection = new Oauth2OidcScopeCollection();

        // Ensure we have the default openid scope
        $this->assertCount(1, $collection->getOidcScopes());
        $this->assertTrue($collection->hasOidcScope('openid'));
        $openIdScope = $collection->getOidcScope('openid');
        $this->assertEquals('openid', $openIdScope->getIdentifier());
        $this->assertCount(3, $openIdScope->getClaims());
        $this->assertTrue($openIdScope->hasClaim('sub'));
        $this->assertTrue($openIdScope->hasClaim('nonce'));

        $testScope = new Oauth2OidcScope([
            'identifier' => 'test-scope-object',
        ]);

        $scopes = [
            'ignored-scope-identifier' => $testScope,
            new Oauth2OidcScope([
                'identifier' => 'test-duplicate',
                'claims' => ['test-duplicate-scope'],
            ]),
            new Oauth2OidcScope([
                'identifier' => 'test-duplicate',
                'claims' => ['test-duplicate-scope-overwritten'],
            ]),

            Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_EMAIL,

            'test-scope-array-associative-claims-indexed' => ['test-scope-array-associative-claims-indexed-claim'],
            'test-scope-array-associative-claims-associative' => [
                'test-scope-array-associative-claims-associative-claim-identifier' =>
                    'test-scope-array-associative-claims-associative-claim-determiner'
            ],
            'test-scope-array-associative-claims-array' => [
                [
                    'identifier' => 'test-scope-array-associative-claims-array-claim-identifier',
                    'determiner' => 'test-scope-array-associative-claims-array-claim-determiner',
                ]
            ],
            [
                'identifier' => 'test-scope-array-indexed-claims-indexed',
                'claims' => ['test-scope-array-indexed-claims-indexed-claim'],
            ],
        ];

        $this->assertEquals($collection, $collection->setOidcScopes($scopes));

        // Ensure we still got the openid scope
        $this->assertTrue($collection->hasOidcScope('openid'));
        // Oauth2OidcClaim
        $this->assertNull($collection->getOidcScope('ignored-scope-identifier'));
        $this->assertEquals($testScope, $collection->getOidcScope('test-scope-object'));
        // Duplicate identifier
        $this->assertTrue($collection->getOidcScope('test-duplicate')->hasClaim('test-duplicate-scope-overwritten'));
        // Indexed string
        $testScopeIndexedString = $collection->getOidcScope('email');
        $this->assertInstanceOf(Oauth2OidcScopeInterface::class, $testScopeIndexedString);
        $this->assertTrue($testScopeIndexedString->hasClaim('email_verified'));
        // Associative array, indexed claims
        $testScopeAssociativeArrayClaimsIndexed = $collection->getOidcScope('test-scope-array-associative-claims-indexed');
        $this->assertInstanceOf(Oauth2OidcScopeInterface::class, $testScopeAssociativeArrayClaimsIndexed);
        $this->assertEquals('test-scope-array-associative-claims-indexed', $testScopeAssociativeArrayClaimsIndexed->getIdentifier());
        $this->assertTrue($testScopeAssociativeArrayClaimsIndexed->hasClaim('test-scope-array-associative-claims-indexed-claim'));
        // Associative array, associative claims
        $testScopeAssociativeArrayClaimsAssociative = $collection->getOidcScope('test-scope-array-associative-claims-associative');
        $this->assertInstanceOf(Oauth2OidcScopeInterface::class, $testScopeAssociativeArrayClaimsAssociative);
        $this->assertEquals('test-scope-array-associative-claims-associative', $testScopeAssociativeArrayClaimsAssociative->getIdentifier());
        $this->assertTrue($testScopeAssociativeArrayClaimsAssociative->hasClaim('test-scope-array-associative-claims-associative-claim-identifier'));
        $this->assertEquals(
            'test-scope-array-associative-claims-associative-claim-determiner',
            $testScopeAssociativeArrayClaimsAssociative->getClaim('test-scope-array-associative-claims-associative-claim-identifier')->getDeterminer()
        );
        // Associative array, array claims
        $testScopeAssociativeArrayClaimsArray = $collection->getOidcScope('test-scope-array-associative-claims-array');
        $this->assertInstanceOf(Oauth2OidcScopeInterface::class, $testScopeAssociativeArrayClaimsArray);
        $this->assertEquals('test-scope-array-associative-claims-array', $testScopeAssociativeArrayClaimsArray->getIdentifier());
        $this->assertTrue($testScopeAssociativeArrayClaimsArray->hasClaim('test-scope-array-associative-claims-array-claim-identifier'));
        $this->assertEquals(
            'test-scope-array-associative-claims-array-claim-determiner',
            $testScopeAssociativeArrayClaimsArray->getClaim('test-scope-array-associative-claims-array-claim-identifier')->getDeterminer()
        );
        // Indexed array, indexed claims
        $testScopeIndexedArrayClaimsIndexed = $collection->getOidcScope('test-scope-array-indexed-claims-indexed');
        $this->assertInstanceOf(Oauth2OidcScopeInterface::class, $testScopeIndexedArrayClaimsIndexed);
        $this->assertEquals('test-scope-array-indexed-claims-indexed', $testScopeIndexedArrayClaimsIndexed->getIdentifier());
        $this->assertTrue($testScopeIndexedArrayClaimsIndexed->hasClaim('test-scope-array-indexed-claims-indexed-claim'));

        // Add extra scopes
        $extraScopes = [
            new Oauth2OidcScope([
                'identifier' => 'test-duplicate',
                'claims' => ['test-duplicate-scope-overwritten-2'],
            ]),
            new Oauth2OidcScope([
                'identifier' => 'test-scope-extra',
            ]),
        ];
        $this->assertEquals($collection, $collection->addOidcScopes($extraScopes));
        $this->assertTrue($collection->getOidcScope('test-duplicate')->hasClaim('test-duplicate-scope-overwritten-2'));
        $this->assertEquals($testScope, $collection->getOidcScope('test-scope-object'));
        $this->assertTrue($collection->hasOidcScope('test-scope-extra'));

        // Clear Claims
        $this->assertEquals($collection, $collection->clearOidcScopes());
        $this->assertCount(1, $collection->getOidcScopes());
        $this->assertTrue($collection->hasOidcScope('openid'));
    }

    public function testAddOidcScopesInvalidArrayType()
    {
        $collection = new Oauth2OidcScopeCollection();

        $this->expectExceptionMessage('Elements should be of type array, string or ' . Oauth2OidcScopeInterface::class);
        $collection->addOidcScopes([
            new \stdClass(),
        ]);
    }

    public function testGetAddRemoveHasScope()
    {
        $this->mockConsoleApplication();

        $collection = new Oauth2OidcScopeCollection();

        $scope1 = new Oauth2OidcScope([
            'identifier' => 'test-scope1',
        ]);
        $scope2 = new Oauth2OidcScope([
            'identifier' => 'test-scope2',
        ]);

        $this->assertEquals($collection, $collection->addOidcScope(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_EMAIL));
        $this->assertEquals($collection, $collection->addOidcScope([
            'identifier' => 'test-scope-array',
        ]));
        $this->assertEquals($collection, $collection->addOidcScope($scope1));
        $this->assertEquals($collection, $collection->addOidcScope($scope2));

        $this->assertTrue($collection->hasOidcScope('email'));
        $this->assertTrue($collection->hasOidcScope('test-scope-array'));
        $this->assertTrue($collection->hasOidcScope('test-scope1'));
        $this->assertTrue($collection->hasOidcScope('test-scope2'));
        $this->assertFalse($collection->hasOidcScope('test-scope3'));

        $this->assertEquals($scope1, $collection->getOidcScope('test-scope1'));
        $this->assertNull($collection->getOidcScope('test-scope3'));

        $this->assertEquals($collection, $collection->removeOidcScope('test-scope2'));
        $this->assertTrue($collection->hasOidcScope('test-scope1'));
        $this->assertFalse($collection->hasOidcScope('test-scope2'));
    }

    public function testGetDefaultOidcScope()
    {
        $this->mockConsoleApplication();

        $collection = new Oauth2OidcScopeCollection();

        $oidcScope = $collection->getDefaultOidcScope(Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_EMAIL);
        $this->assertTrue($oidcScope->hasClaim('email_verified'));
    }

    public function testGetDefaultOidcScopeUnknownIdentifier()
    {
        $collection = new Oauth2OidcScopeCollection();

        $this->expectExceptionMessage('Invalid $scopeName "non-existing", it must be an OpenID Connect default claims scope (openid, profile, email, address, phone).');
        $collection->getDefaultOidcScope('non-existing');
    }

    public function testGetSupportedScopeAndClaimIdentifiers()
    {
        $this->mockConsoleApplication();

        $collection = new Oauth2OidcScopeCollection([
            'oidcScopes' => [
                'test-scope' => [
                    'test-claim1',
                    'test-claim2',
                ]
            ],
        ]);

        $expectedClaims = ['sub', 'auth_time', 'nonce', 'test-claim1', 'test-claim2'];
        sort($expectedClaims);

        $supportedScopeAndClaims = $collection->getSupportedScopeAndClaimIdentifiers();
        $this->assertEquals(['openid', 'test-scope'], $supportedScopeAndClaims['scopeIdentifiers']);
        $this->assertEquals($expectedClaims, $supportedScopeAndClaims['claimIdentifiers']);
    }

    public function testGetFilteredClaims()
    {
        $this->mockConsoleApplication();

        $collection = new Oauth2OidcScopeCollection([
            'oidcScopes' => [
                'scope1' => [
                    '1.1',
                    '1.2',
                ],
                'scope2' => [
                    '2.1',
                    '2.2',
                ],
            ],
        ]);

        $this->assertEquals([], $collection->getFilteredClaims([]));
        $this->assertEquals(['1.1', '1.2'], array_column($collection->getFilteredClaims(['scope1']), 'identifier'));
        $this->assertEquals(['1.1', '1.2', '2.1', '2.2'], array_column($collection->getFilteredClaims(['scope1', 'scope2']), 'identifier'));
        $this->assertEquals(['2.1', '2.2'], array_column($collection->getFilteredClaims(['scope2']), 'identifier'));
    }

}
