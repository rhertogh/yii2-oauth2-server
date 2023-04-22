Yii2-Oauth2-Server Change Log
=============================
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).  

Please check the [Upgrading Instructions](UPGRADE.md) when upgrading to a newer version.  

[1.0.0-alpha10] - Unreleased
---------------------------

### Added
### Changed
- Upgraded `league/oauth2-client` to v2.7.0 to support PKCE natively. (rhertogh)
### Deprecated
### Removed
### Fixed
### Improved
- Cancel running GitHub jobs in progress when PR is updated (rhertogh)
### Security


[1.0.0-alpha9] - 2023-03-22
---------------------------

### Security
- Upgraded `league/oauth2-server` to v8.4.1 which [includes a fix to prevent PKCE Downgrade Attack](https://github.com/thephpleague/oauth2-server/pull/1326). (rhertogh)


[1.0.0-alpha8] - 2023-03-22
---------------------------

### Fixed
- Include `redirectUri` in `Oauth2ClientAuthorizationRequest::__serialize()`.
  Fixes `Oauth2ClientAuthorizationRequest::isClientIdentifiable()`, which in turn caused client authorization to always be
  required in case the authorization request needed to be stored between requests (e.g. when the user was not logged in). (rhertogh)


[1.0.0-alpha7] - 2023-03-21
---------------------------

### Added
- Support for `skipAuthorizationIfScopeIsAllowed` in `Oauth2Module::createClient()` (rhertogh)


[1.0.0-alpha6] - 2022-11-13
---------------------------

### Added
- `Oauth2UserInterface::isOauth2ClientAllowed()` to support access restriction to user/client/grant combinations. (rhertogh)
- Sample app now includes client for 'Client Credentials' grant without a user. (rhertogh)
- Support for "personal access tokens" (see `Oauth2Module::generatePersonalAccessToken()`). (rhertogh)
- `Oauth2UserPatTrait` for easy generating "personal access tokens" from the user model. (bada02)
- `Oauth2ScopeInterface::APPLIED_BY_DEFAULT_IF_REQUESTED` to support allowing scopes for clients without user approval. (rhertogh)
- Check for openssl php extension when using JWKS. (rhertogh)

### Changed
- `Oauth2ClientAuthorizationRequestInterface::getScopesAppliedByDefaultAutomatically()` is renamed to `getScopesAppliedByDefaultWithoutConfirm()`. (rhertogh)
### Removed
- Removed `Oauth2PasswordGrantUserComponentInterface` in favor of events and `Oauth2UserInterface::isOauth2ClientAllowed()`. (rhertogh)

### Fixed
- Mysql port configuration now uses separated port parameter. (rhertogh)

### Improved
- Test coverage


[1.0.0-alpha5] - 2022-09-08
---------------------------

### Added
- PostgreSQL compatibility. (mtangoo, rhertogh)

### Improved
- Optimized tests to reuse database fixtures. (rhertogh)


[1.0.0-alpha4] - 2022-08-20
---------------------------

### Added
- Added setters for common properties of Oauth2Client. (rhertogh)
- Allow configuration of Oauth2ClientScopes in `Oauth2Module::createClient()`. (rhertogh)

### Fixed
- `Oauth2ClientAuthorizationRequestInterface::isAuthorizationNeeded()` now correctly adheres to `Oauth2Client::skipAuthorizationIfScopeIsAllowed()`. (rhertogh)
- Compatibility for lcobucci/jwt 4.2.x causing "Lcobucci\JWT\Signer\InvalidKeyProvided: Key cannot be empty". (rhertogh)


[1.0.0-alpha3] - 2022-08-19
---------------------------

### Added
- Support for Client Secret Rotation. (rhertogh)
- Support for Encryption Key Rotation. (rhertogh)
- Added `Oauth2ClientInterface::setGrantTypes()`. (rhertogh)
- Support `Oauth2BaseClientAuthorizationRequest` "Max Age" without OIDC. (rhertogh)

### Fixed
- Accept string array for `$scopes` parameter in `Oauth2Module::createClient`. (rhertogh)

### Changed
- Changed signature for `Oauth2Module::createClient` to make `$secret` optional. (rhertogh)


[1.0.0-alpha2] - 2022-05-27
---------------------------

### Added
- Support for custom scope authorization message. (rhertogh)
- Allow more easily customization of scopes by merging claims of previously defined scopes. (rhertogh)
- Added `Oauth2Module::createClient()` method to aid in the programmatic creation of clients. (rhertogh)
- Added documentation for OIDC claims. (rhertogh)
- Support for PHP 8.1. (rhertogh)

### Fixed
- Using correct access token TTL (rhertogh)
- Type-casted the type so the `Oauth2Client::isConfidential()` function works as intended. (Roosh Ak)

### Improved
- Several code style fixes. (rhertogh)


[1.0.0-alpha] - 2021-11-11
--------------------------

### Added
- Initial release. (rhertogh)
