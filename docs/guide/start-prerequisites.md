What do you need to know before installing the Yii2-Oauth2-Server
=================================================================

Although this package aims to be as self-explanatory as possible, some prior knowledge is required.

## Oauth2 (and OpenID Connect)

There are a lot of terminology specific Oauth2 and OpenID Connect like "grant types", "clients", "scopes" and "claims".
It is *strongly* recommended that you get familiar with Oauth2 (and OpenID Connect if applicable) before continuing
the installation and setup.  

#### Good places to start are:
* https://developer.okta.com/blog/2019/10/21/illustrated-guide-to-oauth-and-oidc
* https://www.loginradius.com/blog/async/oauth2
* https://aaronparecki.com/oauth-2-simplified
* https://alexbilbie.com/guide-to-oauth-2-grants
* https://www.oauth.com/oauth2-servers/getting-ready

#### Especially the concept of "scopes" is often misunderstood:
> Scopes only come into play in delegation scenarios, and always limit what an app can do on behalf of a user:
> a scope cannot allow an application to do more than what the user can do.

More information [On The Nature of OAuth2’s Scopes](https://auth0.com/blog/on-the-nature-of-oauth2-scopes/)

## Yii2

Since this package is aimed at Yii2, knowledge about [Yii2 usage](https://www.yiiframework.com/doc/guide/2.0/en) is required.
For example how to run [migrations](https://www.yiiframework.com/doc/guide/2.0/en/db-migrations) and what the 
[User Component](https://www.yiiframework.com/doc/guide/2.0/en/security-authentication#configuring-user) and 
[Identity Class](https://www.yiiframework.com/doc/guide/2.0/en/security-authentication#implementing-identity) are. 
