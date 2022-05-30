Encryption Key Rotation
=======================

Similar to changing passwords, regularly rotating the server storage encryption keys is a security best 
practice. When rotating the encryption keys a seamless transition to the new key is crucial to avoid downtime.

Configuring Multiple Keys
-------------------------

In order to support key rotation the Yii2Oauth2Server supports the configuration of multiple encryption keys.  
These can be set in `Oauth2Module::$storageEncryptionKeys`.

> Warning: Keys must remain in `Oauth2Module::$storageEncryptionKeys` as long there is data encrypted with them.
  Removing or overwriting a key will result in data loss!  
  You can check which keys are in use by running `yii oauth2/encryption/key-usage`.  
  To find the usage for a specific key you can run `yii oauth2/encryption/key-usage --key-name=2022-01-01`.

> Note: New data will be encrypted with the key specified in the `Oauth2Module::$defaultStorageEncryptionKey`.
  Exiting data will not be changed until it's actively rotated to the new key (see below). 

```php
return [
   'modules' => [
       'oauth2' => [
           'class' => rhertogh\Yii2Oauth2Server\Oauth2Module::class,
           // ...
           'storageEncryptionKeys' => [ // For ease of use this can also be a JSON encoded string.
               // The index represents the name of the key, this can be anything you like.
               // However, for keeping track of different keys using (or prefixing it with) a date is advisable.
               '2021-01-01' => getenv('MY_OLD_STORAGE_ENCRYPTION_KEY'), // Original Encryption Key
               '2022-01-01' => getenv('MY_NEW_STORAGE_ENCRYPTION_KEY'), // New Encryption Key
           ],
           'defaultStorageEncryptionKey' => '2022-01-01', // Using the new key as default 
           // ...
       ],
       // ...
   ],
];
```

> Tip: For ease of use `storageEncryptionKeys` can also be a JSON encoded string. This way all different keys can be dynamically loaded
from a single environment variable.

Rotating to a new Key
---------------------

The storage encryption keys can be rotated in the following ways:

* Manually via the console via the `yii oauth2/encryption/rotate-keys` command.  
  By default the `Oauth2Module::$defaultStorageEncryptionKey` will be used, to use another key you can use the
  'key-name' argument, e.g. `yii oauth2/encryption/rotate-keys --key-name=2022-01-01`
  
* Programmatically by calling `\rhertogh\Yii2Oauth2Server\Oauth2Module::rotateStorageEncryptionKeys()`
  optionally specifying the `$newKeyName` parameter (if not specified the 
  `Oauth2Module::$defaultStorageEncryptionKey` will be used).

After the encryption keys have been rotated to the new key ensure the old key(s) are no longer used by running
`yii oauth2/encryption/key-usage`.  
When the old keys are no longer used they can be safely removed from the `Oauth2Module::$storageEncryptionKeys`.
