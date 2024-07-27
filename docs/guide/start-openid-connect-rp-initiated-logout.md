OpenID Connect - RP-Initiated Logout for the Yii2-Oauth2-Server
===============================================================

The Yii2 Oauth2 Server supports Single Logout (a.k.a. Single Sign-out) via the
[OpenID Connect RP-Initiated Logout](https://openid.net/specs/openid-connect-rpinitiated-1_0.html) protocol.
This specification defines a mechanism for a "Relying Party" (e.g. a Client) to request that the server logs out the End-User.

Enabling RP-Initiated Logout
----------------------------
For security RP-Initiated Logout is disabled by default. To enable it you can set `Oauth2Module::$openIdConnectRpInitiatedLogoutEndpoint` to `true`.  E.g.:
 ```php
return [
    // ...
    'modules' => [
        'oauth2' => [
            'class' => rhertogh\Yii2Oauth2Server\Oauth2Module::class,
            // ...
            'enableOpenIdConnect' => true,
            'openIdConnectRpInitiatedLogoutEndpoint' => true,
        ],
        // ...
    ],
    // ...
];
```

Each Client also needs to be authorized to initiate the end-user logout. This is done via the `oidc_rp_initiated_logout` property.
This must be one of the `Oauth2ClientInterface::OIDC_RP_INITIATED_LOGOUT_OPTIONS`:  

| Constant                                              | Value | Description                                                                                                 |
|-------------------------------------------------------|-------|-------------------------------------------------------------------------------------------------------------|
| OIDC_RP_INITIATED_LOGOUT_DISABLED                     | 0     | Client is not allowed to initiate logout.                                                                   |
| OIDC_RP_INITIATED_LOGOUT_ENABLED                      | 1     | Client may initiate logout, end-user will be prompted to confirm the logout.                                |
| OIDC_RP_INITIATED_LOGOUT_ENABLED_WITHOUT_CONFIRMATION | 2     | Client can logout the user directly (no prompt), this is useful if the Client is under your direct control. |

The default endpoint is `/oauth2/oidc/end-session`. When enabled, this url will also be visible via the [OpenID Connect Discovery endpoint](docs/guide/start-openid-connect.md#open-id-connect-discovery)
as the `end_session_endpoint` element. 

Post Logout Redirect Uris
-------------------------
By default, the Yii2-Oauth2-Server will redirect the user to the [application's home URL](https://www.yiiframework.com/doc/api/2.0/yii-web-application#getHomeUrl()-detail). 
The Client may request a custom redirect URI via the [`post_logout_redirect_uri` parameter](https://openid.net/specs/openid-connect-rpinitiated-1_0.html#RPLogout).
For security, these URIs have to be specified per Client via the `post_logout_redirect_uris` property. 
The same requirements and options as for the login redirect URIs apply, please see [Yii2-Oauth2-Server Redirect URIs Configuration](start-redirect-uris.md) for more information.

