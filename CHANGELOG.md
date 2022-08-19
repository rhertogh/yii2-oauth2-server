Yii2-Oauth2-Server Change Log
=============================

1.0.0-alpha4 - Under Development
--------------------------------
- Enh: Added setters for common properties of Oauth2Client (rhertogh)
- Bugfix: `Oauth2ClientAuthorizationRequestInterface::isAuthorizationNeeded()` now correctly adheres to `Oauth2Client::skipAuthorizationIfScopeIsAllowed()`


1.0.0-alpha3 (2022-08-19)
-------------------------

- Enh: Added support for Client Secret Rotation (rhertogh)
- Enh: Added support for Encryption Key Rotation (rhertogh)
- Bugfix: Accept string array for `$scopes` parameter in `Oauth2Module::createClient` (rhertogh)
- Enh: Changed signature for `Oauth2Module::createClient` to make `$secret` optional (rhertogh)
- Enh: Added `Oauth2ClientInterface::setGrantTypes()` (rhertogh)
- Enh: Support `Oauth2BaseClientAuthorizationRequest` "Max Age" without OIDC (rhertogh)


1.0.0-alpha2 (2022-05-27)
-------------------------

- Enh: Added support for custom scope authorization message (rhertogh)
- Enh: Several code style fixes (rhertogh)
- Bugfix: Using correct access token TTL (rhertogh)
- Enh: Allow more easily customization of scopes by merging claims of previously defined scopes (rhertogh)
- Added Documentation for OIDC claims (rhertogh)
- Bugfix: Type-casted the type so the `Oauth2Client::isConfidential()` function works as intended (Roosh Ak)
- Added Support for PHP 8.1 (rhertogh)
- Enh: Added `Oauth2Module::createClient()` method to aid in the programmatic creation of clients (rhertogh)


1.0.0-alpha (2021-11-11)
------------------------

- Initial release (rhertogh)
