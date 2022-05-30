Security - Introduction
=======================

Sensitive data stored by the Yii2Oauth2Server is stored encrypted. The encryption key(s) can be configured via the
`Oauth2Module::$storageEncryptionKeys` setting. From the available keys the default key for newly encrypted data can 
be set with the `Oauth2Module::$defaultStorageEncryptionKey` setting.

> Note: While the Yii2Oauth2Server supports key rotation, the underlying
  [oauth2 library](https://github.com/thephpleague/oauth2-server) does not.  
  This means that when changing the encryption key for the authorization and refresh codes
  (`Oauth2Module::$codesEncryptionKey`) would immediately invalidate the authorization and refresh codes.
  Depending on your client type, end users might be required to re-authenticate.


Key/Secret Rotation
-------------------
Key rotation is when an encryption key is retired and replaced by generating a new cryptographic key.
Rotating keys on a regular basis is an industry standard and follows cryptographic best practices, 
the same applies for (client) secrets. 

The Yii2Oauth2Server supports seamless rotation of encryption keys and client secrets. For more information please see:
* [Client Secret Rotation](security-client-secret-rotation.md)
