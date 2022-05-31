Frequently Asked Questions
==========================

This FAQ describes common questions and errors and provides possible solutions for them.

Error Messages
--------------

* ### Error: Unable to read key from file -----BEGIN RSA PRIVATE KEY----- ...  
  This error could appear if the private key is set as string containing a private key with 
  a 'passphrase' but the `$privateKeyPassphrase` is incorrect or not set.

* ### Nginx returns 403 Forbidden for /.well-known/openid-configuration
  Nginx might be configured to protect hidden files and folder from being read from the web.  
  Check your nginx configuration file for:  
  ```nginxconf
  location ~* /\. {
      deny all;
  }
  ```
  And change it to:
  ```nginxconf
  location ~ /\.(?!well-known).* {
      deny all;
  }
  ```
  This will deny access to all files and folders starting with `.` except `.well-known`.

* ### Exception: Getting unknown property: rhertogh\Yii2Oauth2Server\models\Oauth2Client::old_secret
  Version 1.0.0-alpha3 introduced two new columns, since alpha releases are not incremental till the first stable
  release they need to be added manually. Please see the [Upgrading Instructions](../../UPGRADE.md#upgrade-from-v100-alpha2)

Encryption
----------

* ### Help, I lost my encryption key!
  Well, you're on your own. Until quantum computers get powerful enough there is no one that can help you.
  And even quantum computers might not be able to crack symmetric encryption[^1].

[^1]: [Will Symmetric and Asymmetric Encryption Withstand the Might of Quantum Computing?](
https://www.toolbox.com/it-security/encryption/articles/will-symmetric-and-asymmetric-encryption-withstand-the-might-of-quantum-computing)
