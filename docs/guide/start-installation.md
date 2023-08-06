Installing the Yii2-Oauth2-Server
=================================

This guide assumes you have already got Yii2 installed and running.
If not, [install Yii2](https://www.yiiframework.com/doc/guide/2.0/en/start-installation) first.

Prerequisites
-------------
If you haven't done so please read [What do you need to know before installing the Yii2-Oauth2-Server](start-prerequisites.md)
before continuing the installation.

Installation
------------
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

To install the latest stable version of the Yii2-Oauth2-Server run:
```bash
composer require rhertogh/yii2-oauth2-server
```

Configuration
-------------

> Note: This guide uses the terms "Authorization Server" and "Resource Server".
> Most likely your application performs both roles at the same time,
> this is also the default configuration for the Yii2-Oauth2-Server.

1. ### Generating public and private keys
   The public/private key pair is used to sign and verify JWTs transmitted.

   > Note: The private key must be kept secret at all time
   > (i.e. out of the web-root of the authorization server and out of version control).
   
    - To generate your own private key run this command on the terminal:  
      `openssl genrsa -out private.key 2048`  
      If you want to provide a passphrase for your private key run this command instead:  
      `openssl genrsa -aes128 -passout pass:_your_passphrase_ -out private.key 2048`

    - Then extract the public key from the private key:  
      `openssl rsa -in private.key -pubout -out public.key`  
      or use your passphrase if provided on private key generation:  
      `openssl rsa -in private.key -passin pass:_your_passphrase_ -pubout -out public.key`
    
    - In case you used a passphrase it is advisable to store it in an environment variable.
      For the sample app we use `YII2_OAUTH2_SERVER_PRIVATE_KEY_PASSPHRASE`.
   
   #### Distributing the keys:

   The Authorization Server possesses both the private key to sign tokens and the public key to make it available
   as JWKS for clients. The Resource Server possesses only the public key to verify the signatures.  
   
   The keys can be provided to the server in two ways:

   * As file:
     - Set the permissions of the .key files `chmod 660 *.key`,
       change the owner if necessary (e.g. `chown root:www-data`).
     - Set the `privateKey` setting to `'file:///path/to/private.key'`, and the
       `publicKey` to `'file:///path/to/public.key'`.
       
   * As environment variables:
     - Store the content of the `private.key` and `public.key` in environment variables,
       for example `YII2_OAUTH2_SERVER_PRIVATE_KEY` and `YII2_OAUTH2_SERVER_PUBLIC_KEY` respectively.
     - Set the `privateKey` setting to `getenv('YII2_OAUTH2_SERVER_PRIVATE_KEY')`, and the
       `publicKey` to `getenv('YII2_OAUTH2_SERVER_PUBLIC_KEY')`.

   If a passphrase has been used to generate private key it must be provided to the authorization server by setting
   the `privateKeyPassphrase` setting, e.g `getenv('YII2_OAUTH2_SERVER_PRIVATE_KEY_PASSPHRASE')`. 

   The public key should be distributed to any services (e.g. the resource servers) that validate access tokens.

2. ### Generating encryption keys
   Encryption keys are used to encrypt authorization and refresh codes.

    - To generate 2 keys for the server run the following command in the terminal twice:
      `vendor/bin/generate-defuse-key`

    - It is advisable to store the two generated keys in environment variables.
      For the sample app we use `YII2_OAUTH2_SERVER_CODES_ENCRYPTION_KEY`
      and `YII2_OAUTH2_SERVER_STORAGE_ENCRYPTION_KEY`

3. ### Application Configuration
   Once the extension is installed, simply modify your application configuration as follows:

   ```php
   return [
       'bootstrap' => [
            'oauth2',
            // ...
       ],
       'modules' => [
           'oauth2' => [
               'class' => rhertogh\Yii2Oauth2Server\Oauth2Module::class,
               'identityClass' => app\models\User::class, // The Identity Class of your application (most likely the same as the 'identityClass' of your application's User Component) 
               'privateKey' => 'file:///path/to/private.key', // Path to the private key generated in step 1. Warning: make sure the path is outside the web-root.
               'publicKey' => 'file:///path/to/public.key', // Path to the public key generated in step 1.
               'privateKeyPassphrase' => getenv('YII2_OAUTH2_SERVER_PRIVATE_KEY_PASSPHRASE'), // The private key passphrase (if used in step 1).
               'codesEncryptionKey' => getenv('YII2_OAUTH2_SERVER_CODES_ENCRYPTION_KEY'), // The encryption key generated in step 2.
               'storageEncryptionKeys' => [ // For ease of use this can also be a JSON encoded string.
                   // The index represents the name of the key, this can be anything you like.
                   // However, for keeping track of different keys using (or prefixing it with) a date is advisable.
                   '2021-01-01' => getenv('YII2_OAUTH2_SERVER_STORAGE_ENCRYPTION_KEY'), // The encryption key generated in step 2.
               ],
               'defaultStorageEncryptionKey' => '2021-01-01', // The index of the default key in storageEncryptionKeys 
               'grantTypes' => [ // For more information which grant types to use, please see https://oauth2.thephpleague.com/authorization-server/which-grant/
                   Oauth2Module::GRANT_TYPE_AUTH_CODE,
                   Oauth2Module::GRANT_TYPE_REFRESH_TOKEN,
                   
                   // Other possibilities are:
                   // Oauth2Module::GRANT_TYPE_CLIENT_CREDENTIALS,
                   
                   // Legacy possibilities (not recommended, but still supported) are:
                   // Oauth2Module::GRANT_TYPE_IMPLICIT, // Legacy Grant Type, see https://oauth.net/2/grant-types/implicit/
                   // Oauth2Module::GRANT_TYPE_PASSWORD, // Legacy Grant Type, see https://oauth.net/2/grant-types/password/
                ],
                'migrationsNamespace' => 'app\\migrations\\oauth2', // The namespace with which migrations will be created (and by which they will be located).
                'enableOpenIdConnect' => true, // Only required if OpenID Connect support is required
           ],
           // ...
       ],
       'controllerMap' => [
           'migrate' => [
               // ...
               'migrationNamespaces' => [
                   // ...
                   'app\\migrations\\oauth2', // Add the `Oauth2Module::$migrationsNamespace` to your Migration Controller  
               ],
           ],
       ],
       // ...
   ];
   ```

User Identity Class
-------------------
In order to support Oauth 2.0 your User Identity Class (a.k.a. the User Model) must implement
`rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserInterface`

```php
<?php
namespace app\models;
//...
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2UserInterface;
use rhertogh\Yii2Oauth2Server\traits\models\Oauth2UserIdentityTrait;
//...

class User extends ActiveRecord implements IdentityInterface, Oauth2UserInterface
{
    use Oauth2UserIdentityTrait; # You can use the trait or implement the Oauth2UserInterface yourself.
    // ...
}
```

Migrations
----------
Since this package closely connects to your User Model, and its corresponding table, the creation date/time of the 
migration matters. In order to solve this running the migrations for the Yii2-Oauth2-Server is a 2-step process:
1. Generation of the migrations:  
   * Make sure the `migrationsNamespace` of the Oauth2Module is set (e.g. `'app\\migrations\\oauth2'`).  
     Note: The specified namespace must be defined as a Yii alias (e.g. `'@app'`). 
   * Run `./yii oauth2/migrations/generate`
     * Depending on your configuration one or more migrations will be proposed to generate.
     * Check the file paths and confirm the generation (or add `--interactive=0` as option to the `generate` command).
     * The migrations should now have been successfully generated.

2. Run the migrations:
  * Make sure the `migrationsNamespace` as specified for the Oauth2Module is included in the `migrationNamespaces`
    of your Migration Controller.
  * Run your migration command as usual (most likely `./yii migrate`).
    * The migrations generated in step 1 should be ready to be applied.
    * Confirm the application the migrations (or add `--interactive=0` as option to the `migrate` command).

> Note: The generated migrations depend on the configuration of the Oauth2Module.
> For example, the migration for OpenID Connect scopes is only generated when OpenID Connect is enabled.
> After changing the configuration repeating steps 1 and 2 might be required.
> The same applies to updating the Yii2-Oauth2-Server which might introduce new migrations.
> In any case, only newly required migrations will be generated.

Authenticating Client requests
------------------------------

### HTTP Bearer Authentication
The most common way of accessing OAuth 2.0 APIs is using a “Bearer Token”.
This is a single string which acts as the authentication of the API request, sent in an HTTP “Authorization” header.
To assist in implementing this the Yii2-Oauth2-Server includes a Yii2 action filter that supports the authentication 
method based on HTTP Bearer token:  
`\rhertogh\Yii2Oauth2Server\filters\auth\Oauth2HttpBearerAuth`

If you want to add HTTP Bearer Authentication to your API you can easily add it to a base controller.
For example:
```php
<?php
namespace app\modules\api\controllers\base;

use rhertogh\Yii2Oauth2Server\filters\auth\Oauth2HttpBearerAuth;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;

abstract class BaseController extends Controller
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                // Use Oauth2HttpBearerAuth. To support multiple authentication methods please see:
                // https://www.yiiframework.com/doc/guide/2.0/en/rest-authentication#authentication
                'class' => Oauth2HttpBearerAuth::class,
            ],
        ]);
    }
}
```
After creating the base controller for your api, you can extend it to create a controller. For example:
```php
<?php
namespace app\modules\api\controllers;

use app\modules\api\controllers\base\BaseController;
use Yii;

class UserController extends BaseController
{
    public function actionMe()
    {
        // Warning: when returning a model make sure you implement its `field()` method (see Exposing data in the Installing the Yii2-Oauth2-Server guide).
        return Yii::$app->user->identity;
    }
}
```

Exposing Data
-------------
Although not Oauth specific, it is worth noting that by default Yii2 exposes all public properties of an object
(or attributes in case of a Model). Therefore it is important to implement the `fields()` method on classes that you
return in your API. For more information, please see [Data Exporting](
https://www.yiiframework.com/doc/guide/2.0/en/structure-models#data-exporting) section in the Yii2 Guide.
To protect the User model we used in the example above you could use the following example:
```php
<?php
namespace app\models;
//...
use yii\web\IdentityInterface;
//...

class User extends ActiveRecord implements IdentityInterface
{
    public function fields()
    {
        return [
            'id',
            // ... (Define other fields here that are safe to share)
        ];
    }
}
```

Defining a Client
-----------------
A new client can be defined in one of the following ways:
 * Manually via the command line with the `./yii oauth2/client/create` command. 
 * Programmatically via either:
   * The `\rhertogh\Yii2Oauth2Server\Oauth2Module::createClient()` function.
   * By creating a new `\rhertogh\Yii2Oauth2Server\models\Oauth2Client` model and, if required, 
     `\rhertogh\Yii2Oauth2Server\models\Oauth2ClientScope` models and saving them.
 * Directly creating a record in the client table (named `oauth2_client` by default) and, if required, adding
   records in the client-scope junction table (named `oauth2_client_scope` by default).  
   > Note: this option is *not* recommended since it requires manual encryption of the secrets.

### Redirect URIs
Redirect URLs are a critical part of the OAuth 2.0 flow. After a user successfully authorizes an application,
the authorization server will redirect the user back to the application.
Because the redirect URL will contain sensitive information, it is critical that the service doesn't redirect the user 
to arbitrary locations.  
To ensure the user will only be redirected to appropriate locations it is required to register one or more redirect URLs
when defining a client.

The redirect URIs can be set during the creation of the `Oauth2Module::createClient()` via the `$redirectUris` parameter
or via the `Oauth2Client::setRedirectUri()` method.  
In both cases a string or array of strings can be used, e.g.:
```php
[
    'https://localhost:4200/auth/return/',
    'https://app.my-domain.com/auth/return/',
]
```
When enabled, it's also possible to use environment variables inside the redirect urls, e.g.:
```php
[
    'https://${MY_APPLICATION_DOMAIN}/auth/return/',
]
```
Please see the [Yii2-Oauth2-Server Redirect URIs Configuration](start-redirect-uris.md#using-environment-variables)
on how to configure environment variables substitution.


### Sample client
In order to quickly get started you can create a sample client for [Postman](https://www.postman.com/)
with the following command:  
`./yii oauth2/client/create -v --interactive=0 --sample=postman --secret=your-client-secret`
> Note: Replace "your-client-secret" with a secret of your choosing of at least 10 characters.

Check the [Client Configuration](appendix-client-configuration.md) on how to configure Postman.

Validating Installation
---------------------
To assist in debugging and finding endpoint URLs you can run the `./yii oauth2/debug/config` command.
It will list the current Oauth2Module settings and the server endpoints (along with their settings for customization).

If everything is set up correctly your application now supports Oauth 2.0 🥳

Usage
-----
Since the Yii2-Oauth2-Server integrates with the Yii2 authentication system you can continue to use the user
component (`Yii::$app->user`) and identity (`Yii::$app->user->identity`).
For example (assuming you've set up [RBAC](https://www.yiiframework.com/doc/guide/2.0/en/security-authorization#rbac))
`Yii::$app->user->can('role_you_want_to_check')`.

For further details, please see [Identity, Permissions and Scopes](start-identity-permissions-and-scopes.md)

OpenID Connect
--------------
Please see [OpenID Connect for the Yii2-Oauth2-Server](start-openid-connect.md)
for more information on how to set up OpenID Connect.
