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
