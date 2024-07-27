Authentication Request Prompt
=============================

The [OpenID Connect Core specification](https://openid.net/specs/openid-connect-core-1_0.html#AuthRequest) adds the 
optional Authentication Request parameter "prompt". 
In the Yii2-Oauth2-Server this feature is generalized to any Oauth2 Authentication Request. 

The following prompt values are supported:

| Prompt            | Effect                                                                                                                                 |
|-------------------|----------------------------------------------------------------------------------------------------------------------------------------|
| `none`            | The server does not display any authentication or consent user interface pages.                                                        |
| `login`¹          | The server prompts the End-User for reauthentication.                                                                                  |
| `consent`         | The server prompts the End-User for consent (even if it would otherwise not do so based on  the (client) configuration).               |
| `select_account`¹ | The server prompts the End-User with a user account selection screen.                                                                  |
| `create`²         | The server prompts the End-User to create a new account (whether the user may log in to an existing account is up to the application). |

¹ Please see [OpenID Connect for the Yii2-Oauth2-Server](start-openid-connect.md#user-component) for the required configuration.  
² The `create` prompt requires additional configuration, please see [below](#user-account-creation).  

The available prompt values will also be available via the [OpenID Connect Discovery endpoint](start-openid-connect.md#openid-connect-discovery)
as the `prompt_values_supported` element.

User Account Creation
---------------------
In order to redirect the end-user to the account creation page, the `userAccountCreationUrl` must be configured.
This property can have any value that the Yii2 [`URL::to()`](https://www.yiiframework.com/doc/api/2.0/yii-helpers-url#to()-detail) function accepts.
E.g.:
 ```php
return [
    // ...
    'modules' => [
        'oauth2' => [
            'class' => rhertogh\Yii2Oauth2Server\Oauth2Module::class,
            // ...
            'userAccountCreationUrl' => ['user/register'],
        ],
        // ...
    ],
    // ...
];
```
