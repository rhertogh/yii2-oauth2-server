About the Yii2-Oauth2-Server
============================

What is the Yii2-Oauth2-Server?
-------------------------------

The Yii2-Oauth2-Server is a Yii2 specific implementation for the [PHP League's OAuth2 Server](https://oauth2.thephpleague.com/)
which supports the following RFCs:

* [RFC6749 (OAuth 2.0)](https://tools.ietf.org/html/rfc6749)
* [RFC6750 (The OAuth 2.0 Authorization Framework: Bearer Token Usage)](https://tools.ietf.org/html/rfc6750)
* [RFC7519 (JSON Web Token (JWT))](https://tools.ietf.org/html/rfc7519)
* [RFC7636 (Proof Key for Code Exchange by OAuth Public Clients)](https://tools.ietf.org/html/rfc7636)

On top of Oauth 2 the server also supports [OpenID Connect Core](https://openid.net/specs/openid-connect-core-1_0.html)

Its aim is to provide a quick and secure way to add Oauth2 and OpenID Connect support to any Yii2 project.
It does this for example by encrypting secrets and providing interfaces and traits for your existing User model and
component so that they don't have to be replaced.
