Upgrading Instructions
======================

This file contains the upgrade notes. These notes highlight changes that could break your
application when you upgrade the package from one version to another.  
Even though we try to ensure backwards compatibility (BC) as much as possible, sometimes
it is not possible or very complicated to avoid it and still create a good solution to
a problem.

The Yii2-Oauth2-Server follows [Semantic Versioning 2.0](https://semver.org/spec/v2.0.0.html)  
Please see the [Change Log](CHANGELOG.md) for more information on version history.

> Note: The following upgrading instructions are cumulative. That is, if you want to upgrade 
  from version A to version C and there is version B between A and C, you need to follow the instructions
  for both A and B.

Upgrade from v1.0.0-alpha14
---------------------------

* The `Oauth2EncryptorInterface` has been renamed to `Oauth2CryptographerInterface`.
  This also applies to all related classes and functions like the `Oauth2Cryptographer` itself and 
  `Oauth2Module::getCryptographer`.  
  If you don't define a custom implementation for the interface and don't use the classes and functions directly this
  won't affect you, otherwise you might have to rename the class(es)/function(s).


Upgrade from v1.0.0-alpha13
---------------------------

* The `Oauth2UserInterface` now defines the `getId()` function and
  the `Oauth2ClientInterface` now defines `getMinimumSecretLength` and `setMinimumSecretLength` functions.  
  If you don't define a custom implementation for these interfaces this won't affect you,
  otherwise you might have to implement these functions.


Upgrade from v1.0.0-alpha12
---------------------------

* The Model interfaces and traits have been refactored to use a more generic findByPk() instead of findById().
  If you don't use these interfaces and traits directly this won't affect you.
  - The `Oauth2ModelRepositoryInterface` now extends `Oauth2RepositoryInterface` and introduces `findModelByPk($pk)`.
  - The `Oauth2RepositoryIdentifierTrait` is renamed to `Oauth2ModelRepositoryTrait` and introduces `findModelByPk($pk)`
  - The `Oauth2ActiveRecordIdInterface` and `Oauth2ActiveRecordIdTrait` have been removed,
    their functionality is replaced by the `Oauth2ActiveRecordInterface` and `Oauth2ActiveRecordTrait` respectively.
  - The `Oauth2ClientInterface` has additional getters and setters and a `syncClientScopes()` function. 


Upgrade from v1.0.0-alpha10
---------------------------

* > Note: Database changes will not be incremental till the first stable release.

  v1.0.0-alpha11 introduces a new columns for the `oauth2_client` table.    
  In order to apply these changes you can run the following statements:  
  MySQL:
  ```MySQL
  ALTER TABLE `oauth2_client` ADD COLUMN `allow_variable_redirect_uri_query` TINYINT(1) NOT NULL DEFAULT 0 AFTER `redirect_uris`;
  ```
  PostgeSQL:
  ```SQL
  ALTER TABLE `oauth2_client` ADD COLUMN `allow_variable_redirect_uri_query` BOOLEAN NOT NULL DEFAULT false AFTER `redirect_uris`;
  ```


Upgrade from v1.0.0-alpha5
--------------------------

* > Note: Database changes will not be incremental till the first stable release.

  v1.0.0-alpha6 introduces a new columns for the `oauth2_client` table.    
  In order to apply these changes you can run the following statements:  
  MySQL:  
  ```MySQL
  ALTER TABLE `oauth2_client` ADD COLUMN `end_users_may_authorize_client` TINYINT(1) NOT NULL DEFAULT 1 AFTER `scope_access`;
  ```
  PostgeSQL:  
  ```SQL
  ALTER TABLE `oauth2_client` ADD COLUMN `end_users_may_authorize_client` BOOLEAN NOT NULL DEFAULT true AFTER `scope_access`;
  ```

* The interface `\rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2UserInterface` defines a new method
  `isOauth2ClientAllowed()`. This method determines if a user can use the client and/or grant.  
  If all of your users may access any Oauth2 client and all grant types you can add the following function to your
  user identity class (e.g. `app\models\User`):
  ```php
  public function isOauth2ClientAllowed($client, $grantType)
  {
      return true; // Allow all users to use all clients with any grant type.
  }
  ```

* The `rhertogh\Yii2Oauth2Server\interfaces\components\user\Oauth2PasswordGrantUserComponentInterface` has been removed
  in favor of events and the `Oauth2UserInterface::isOauth2ClientAllowed()`.  
  In case your code relied on the `beforeOauth2PasswordGrantLogin()` method, you can now use the 
  `isOauth2ClientAllowed()` method (which is more flexible and is called for all grant types).
  As a replacement for `afterOauth2PasswordGrantLogin($identity, $grant)` you can register an event handler for the
  `Oauth2Module::EVENT_AFTER_ACCESS_TOKEN_ISSUANCE` event.
  
* The `\rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ClientAuthorizationRequestInterface`
  defines a new method `isAuthorizationAllowed()`.  
  The default implementation calls the new `Oauth2ClientInterface::endUsersMayAuthorizeClient()` (see below).

* The `\rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface` defines a new method 
  `endUsersMayAuthorizeClient()` to determine if an end-user is allowed to authorize an Oauth2 client.    
  The default implementation uses the `oauth2_client.end_users_may_authorize_client` database field.
  
* The method `\rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ClientAuthorizationRequestInterface::getScopesAppliedByDefaultAutomatically()`  
  has been renamed to: `\rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ClientAuthorizationRequestInterface::getScopesAppliedByDefaultWithoutConfirm()`  
  This most likely only affect you if you use your own implementation of the `Oauth2ClientAuthorizationRequestInterface`


Upgrade from v1.0.0-alpha2
--------------------------

* > Note: Database changes will not be incremental till the first stable release.   
  
  v1.0.0-alpha3 introduces two new columns for the `oauth2_client` table.    
  In order to apply these changes you can run the following statements:
  ```SQL
  ALTER TABLE `oauth2_client` ADD COLUMN `old_secret` TEXT AFTER `secret`;
  ALTER TABLE `oauth2_client` ADD COLUMN `old_secret_valid_until` DATETIME AFTER `old_secret`;
  ```

* The signature for `\rhertogh\Yii2Oauth2Server\Oauth2Module::createClient()` has changed.
  The `$type` and `$secret` parameters have been moved and `$secret` is now optional.
  If you use this method you'll need to update it accordingly.

* The namespace for the User identity model interfaces has changed  
  from `rhertogh\Yii2Oauth2Server\interfaces\models`  
  to `rhertogh\Yii2Oauth2Server\interfaces\models\external\user`  
  
  This affects the following interfaces:
  * `Oauth2PasswordGrantUserInterface`
  * `Oauth2OidcUserSessionStatusInterface`
  * `Oauth2UserInterface`
  * `Oauth2OidcUserInterface`
  
  You will have to update their imports (`use` statements) in your User identity model accordingly.
