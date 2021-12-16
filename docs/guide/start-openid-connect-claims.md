OpenID Connect Claims
=====================

In OpenID Connect a "claim" is a piece of information asserted about an Entity, e.g. `name` or `birthdate`.
For more information about claims please see
[Identity, Claims, & Tokens – An OpenID Connect Primer](https://developer.okta.com/blog/2017/07/25/oidc-primer-part-1)

Specifying Custom Claims and Overwriting Default OIDC Claims 
------------------------------------------------------------
The OpenID Connects specifies several claims by default
([OIDC Standard Claims](https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims)).
These claims are grouped in [Scopes](https://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims).
If you want, you can add custom scopes and claims or customize the default ones via the `openIdConnectScopes` setting. 
The `openIdConnectScopes` is a multidimensional array in the format:
```php
[
    'my_first_scope' => [ // the array key represents the name of the scope.
        
        // Simple claim without configuration.
        'claim_1', // in this case the name of the claim is also used for determining its value (see below).
        
        // Advanced claim configuration where the array key is used as claim "name" and the value as "determiner".  
        'claim_2' => [MyCustomDeterminer::class, 'myCustomFunction'], // A callable with the signature `function(User $identity, Oauth2OidcClaimInterface $claim, Oauth2Module $module)`.
        'claim_3' => 'myCustomFunction', // The name of a function on the Identity
        'claim_4' => 'custom_property', // The name of a property on the Identity
        'claim_5a' => 'custom_relation.custom_property', // A path to a nested property (see `\yii\helpers\ArrayHelper`)
        'claim_5b' => ['custom_relation', 'custom_property'], // A path to a nested property (see `\yii\helpers\ArrayHelper`)
    ],
    'my_second_scope' => [
        // ...
    ],
]
```
E.g.:
 ```php
return [
    // ...
    'modules' => [
        'oauth2' => [
            'class' => rhertogh\Yii2Oauth2Server\Oauth2Module::class,
            // ...
            'enableOpenIdConnect' => true,
            'openIdConnectScopes' => [
                ...Oauth2OidcScopeCollectionInterface::OPENID_CONNECT_DEFAULT_SCOPES, // include default scopes/claims.
                Oauth2OidcScopeInterface::OPENID_CONNECT_SCOPE_PROFILE => [ // specify custom value determiners for default scope.
                    Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_GIVEN_NAME => 'first_name', // map the default claim 'given_name' to the 'first_name' property. 
                    Oauth2OidcClaimInterface::OPENID_CONNECT_CLAIM_NAME => 'fullName', // Most likely your model extends `\yii\base\BaseObject` which means you can use a magic property, e.g. `getFullName()`. 
                    'user_name', // custom claim added to default scope
                ],
                'groups' => [ // custom scope
                    'groups', // custom claim
                    'defaultGroup', // custom claim
                ],
                
            ],
        ],
        // ...
    ],
    // ...
];
```
