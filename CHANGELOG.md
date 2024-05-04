Yii2-Oauth2-Server Change Log
=============================
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).  

Please check the [Upgrading Instructions](UPGRADE.md) when upgrading to a newer version.  

[1.0.0-alpha18] - Unreleased
----------------------------

### Added
- Support for OpenID Connect RP-Initiated Logout. (rhertogh)
- Added `Oauth2Module::$httpClientErrorsLogLevel` in order to specify a log level for HTTP client error responses. (rhertogh)
- Added DB driver info and logo to .bashrc
- Support for SQLite. (rhertogh)
- Ability to run sample app from a single Docker container (using SQLite). (rhertogh)
- Added setters for module repositories. (rhertogh)
- Added support for custom claims in access token and include `client_id` by default. (rhertogh)

### Changed
- The `tests` now use the same environment variable name for the DB driver as the sample app (`YII2_OAUTH2_SERVER_TEST_DB_DRIVER` -> `YII_DB_DRIVER`). (rhertogh)
- The namespace and inheritance of `Oauth2OidcBearerTokenResponse` have changed to allow the usage of a more generic `Oauth2BearerTokenResponse`. (rhertogh)

### Deprecated

### Removed

### Fixed
- Allow `null` values for `Oauth2Client` `$uri` and `$postLogoutRedirectUris`. (rhertogh) 

### Improved
- Added health checks to docker compose files for MySQL and PostgreSQL. (rhertogh)
- Postman can now be used to connect to the `tests` instance (redirect_uri has been added). (rhertogh)

### Security


[1.0.0-alpha17] - 2023-11-27
----------------------------

### Added
- Require TLS connection except for localhost (can be configured via `Oauth2Module::$nonTlsAllowedRanges`). (rhertogh)

### Changed
- `Oauth2ClientInterface` "ScopeAccess" has been split into "AllowGenericScopes" and "ExceptionOnInvalidScope". (rhertogh)
- The `$unknownScopes` parameter has been added to `Oauth2ClientInterface::validateAuthRequestScopes()` (rhertogh)
- The `$requestedScopeIdentifiers` parameter for `Oauth2ClientInterface::getAllowedScopes()` now accepts `true` which will return all available scopes. (rhertogh)

### Removed
- `Oauth2ClientInterface` "ScopeAccess" related constants. (rhertogh)

### Fixed
- Incorrect ClientScope relation in `Oauth2Client::getAllowedScopes()`. (rhertogh)

### Improved
- The `yii oauth2/client/view` console command now supports scopes.  (rhertogh)


[1.0.0-alpha16] - 2023-11-02
----------------------------

### Added
- It's now possible to specify "environment variables configuration" for Oauth2 Clients which allows env var usage in secrets and redirect URIs. (rhertogh)
- You can now generate a secret via the command line (`yii oauth2/encryption/generate-secret`). (rhertogh)


[1.0.0-alpha15] - 2023-08-04
----------------------------

### Added
- The `EnvironmentHelper` class was added with the function `parseEnvVars()` to aid in the replacing of env vars in strings. (rhertogh)

### Changed
- Renamed the `Oauth2EncryptorInterface` (and all related classes/functions) to `Oauth2CryptographerInterface` to better reflect its purpose and future use. (rhertogh) 
- The `redirectUris` for the `Oauth2Client` now supports an env var that contains a JSON array and now requires used env vars to be allow-/deny-listed. (rhertogh)

### Security
- Upgraded `league/oauth2-server` to v8.4.2 for https://github.com/advisories/GHSA-wj7q-gjg8-3cpm. (rhertogh)


[1.0.0-alpha14] - 2023-07-17
----------------------------

### Added
- Support for default values in `Oauth2GeneratePatAction`. (rhertogh)
- The `Oauth2ClientInterface` now defines `get`- and `set`-`MinimumSecretLength`.  (rhertogh)
- Support for importing data from other projects. (rhertogh)

### Fixed
- The `Oauth2UserInterface` now defines the necessary `getId()` function. (rhertogh)

### Improved
- Refactored and added tests for defaultAccessTokenTTL. (rhertogh)
- Made PHP CodeSniffer and PHP CS fixer happier. (rhertogh)
- Only advice user to add Oauth2 migration namespace to `migrationNamespaces` if not yet done. (rhertogh) 


[1.0.0-alpha13] - 2023-06-19
----------------------------

### Added
- Added `Oauth2Module::$openIdConnectProviderConfigurationInformationPath` to configure OIDC config info endpoint. (rhertogh) 
- Additional getters and setters for Client properties. (rhertogh)
- Added `Oauth2ClientInterface::syncClientScopes()` to add/remove/update the client-scope relation. (rhertogh)
- Added CLI controllers for listing/viewing/updating/deleting clients. (rhertogh)

### Changed
- The `Oauth2ModelRepositoryInterface` now extends `Oauth2RepositoryInterface` and introduced `findModelByPk($pk)`.  (rhertogh) 
- The `Oauth2RepositoryIdentifierTrait` is renamed to `Oauth2ModelRepositoryTrait` and introduced `findModelByPk($pk)`.  (rhertogh)

### Removed
- The `Oauth2ActiveRecordIdInterface` and `Oauth2ActiveRecordIdTrait` have been removed, their functionality has been replaced by the `Oauth2ActiveRecordInterface` and `Oauth2ActiveRecordTrait` respectively. (rhertogh)

### Fixed
- Oauth authorization and access token responses set correct `Content-Type: application/json; charset=UTF-8` headers (raimon-segura, rhertogh) (https://github.com/rhertogh/yii2-oauth2-server/issues/13)
- Migrations now handle `tinyint`, `smallint` and `bigint` data types for `user` table primary key correctly (mtangoo, rhertogh) (https://github.com/rhertogh/yii2-oauth2-server/pull/14)

### Improved
- The `\rhertogh\Yii2Oauth2Server\helpers\Psr7Helper::psr7ToYiiResponse()` function now sets the response format as "raw" by default accepts an additional `defaultConfig` parameter. (rhertogh)


[1.0.0-alpha12] - 2023-05-26
----------------------------

### Added
- Support for environment variables in Oauth2Client `redirect_uris`. (rhertogh)

### Improved
- Test coverage. (rhertogh)


[1.0.0-alpha11] - 2023-05-03
----------------------------

### Added
- An Oauth2Client can now be configured to accept a variable query part in the redirect URI (`allow_variable_redirect_uri_query`). (rhertogh)

### Fixed
- Using `true` (instead of `1`) as defautl value for DB column `user.enabled`. (rhertogh)

### Improved
- Generated base models with new `::class` constant. (rhertogh)


[1.0.0-alpha10] - 2023-04-25
----------------------------

### Changed
- Upgraded `league/oauth2-client` to v2.7.0 to support PKCE natively. (rhertogh)

### Removed
- Removed custom implementation for oauth2-client PKCE since it's now supported by the library. (rhertogh)

### Improved
- Cancel running GitHub jobs in progress when PR is updated. (rhertogh)

### Security
- Upgraded `guzzlehttp/psr7` to v2.5.0 for https://github.com/advisories/GHSA-wxmh-65f7-jcvw. (rhertogh)


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
- Support for `skipAuthorizationIfScopeIsAllowed` in `Oauth2Module::createClient()`. (rhertogh)
### Fixed
- Fixed `$clientSecret` passing as true when `$client->isConfidential()` is `false`. (ms48) (https://github.com/rhertogh/yii2-oauth2-server/pull/8)
- Removed `lcobucci/clock` and `symfony/deprecation-contracts` as dependency from `composer.json` to solve version constraint issues (mtangoo, rhertogh) (https://github.com/rhertogh/yii2-oauth2-server/issues/11)


[1.0.0-alpha6] - 2022-11-13
---------------------------

### Added
- `Oauth2UserInterface::isOauth2ClientAllowed()` to support access restriction to user/client/grant combinations. (rhertogh) (https://github.com/rhertogh/yii2-oauth2-server/issues/5)
- Sample app now includes client for 'Client Credentials' grant without a user. (rhertogh)
- Support for "personal access tokens" (see `Oauth2Module::generatePersonalAccessToken()`). (rhertogh)
- `Oauth2UserPatTrait` for easy generating "personal access tokens" from the user model. (bada02) (https://github.com/rhertogh/yii2-oauth2-server/pull/7)
- `Oauth2ScopeInterface::APPLIED_BY_DEFAULT_IF_REQUESTED` to support allowing scopes for clients without user approval. (rhertogh)
- Check for openssl php extension when using JWKS. (rhertogh)

### Changed
- `Oauth2ClientAuthorizationRequestInterface::getScopesAppliedByDefaultAutomatically()` is renamed to `getScopesAppliedByDefaultWithoutConfirm()`. (rhertogh)
### Removed
- Removed `Oauth2PasswordGrantUserComponentInterface` in favor of events and `Oauth2UserInterface::isOauth2ClientAllowed()`. (rhertogh)

### Fixed
- Mysql port configuration now uses separated port parameter. (rhertogh)

### Improved
- Test coverage. (rhertogh)


[1.0.0-alpha5] - 2022-09-08
---------------------------

### Added
- PostgreSQL compatibility. (mtangoo, rhertogh) (https://github.com/rhertogh/yii2-oauth2-server/issues/3)

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
- Using correct access token TTL. (rhertogh)
- Type-casted the type so the `Oauth2Client::isConfidential()` function works as intended. (Roosh Ak) (https://github.com/rhertogh/yii2-oauth2-server/pull/1)

### Improved
- Several code style fixes. (rhertogh)


[1.0.0-alpha] - 2021-11-11
--------------------------

### Added
- Initial release. (rhertogh)
