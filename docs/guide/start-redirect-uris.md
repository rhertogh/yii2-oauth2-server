Yii2-Oauth2-Server Redirect URIs Configuration
==============================================

For most Oauth 2.0 grant types the clients must specify one or more redirect URLs in order to avoid exposing users
to open redirection attacks where an authorization code or access token can be intercepted by an attacker.

## General Information

Some client applications may have multiple places they want to redirect the OAuth 2.0 process back to, it may be
tempting to register multiple redirect URIs, or you may think you need to be able to vary the redirect URL per request.
Instead, OAuth 2.0 provides a mechanism for this, the “state” parameter.  
After the OAuth 2.0 flow completes the client can use the “state” to [redirect the user](
https://auth0.com/docs/secure/attack-protection/state-parameters#redirect-users).

For more information about Oauth 2.0 redirect URIs, please check out 
https://www.oauth.com/oauth2-servers/redirect-uris/. 

### Security

For security the redirect URIs must be a https endpoints to prevent the authorization code from being intercepted
during the authorization process. If your redirect URI is not https, then an attacker may be able to intercept the
authorization code and use it to hijack a session.  
The one exception to this is for apps running on the loopback interface, such as a native desktop application,
or when doing local development.

## General Configuration

The redirect URIs can be set during the creation of the `Oauth2Module::createClient()` via the `$redirectUris` parameter
or via the `Oauth2Client::setRedirectUri()` method.  
In both cases a string or array of strings can be used, e.g.:
```php
// Single URI:
'https://app.my-domain.com/auth/return/'

// As array of URIs:
[
    'https://localhost:4200/auth/return/',
    'https://app.my-domain.com/auth/return/',
]
```

### Variable Redirect URI Query String

The OAuth 2.0 specification states that redirect URIs should match exactly. This means a redirect URL of
https://example.com/auth would not match https://example.com/auth?destination=account.

For client applications that don't adhere to the standard, you can, although not advised, override this restriction by
setting the `Oauth2Client::$allow_variable_redirect_uri_query` to `true`.
> Note: Instead of this workaround client applications should use the Oauth 2.0 “state” parameter.

## Using Environment Variables

To allow dynamic redirect URIs (e.g. [between environments](https://12factor.net/config)) the Oauth2 server can be
configured to substitute environment variables in `redirect_uris`.  
This can be done by configuring the `clientRedirectUriEnvVarConfig` for the Oauth2 module.

> Note: For security all variables in a redirect URL must be allowed match at least 1 pattern the `$allowList`
> (and don't match any in the `$denyList`).

### Configuration

```php
return [
    // ...
    'modules' => [
        'oauth2' => [
            'class' => rhertogh\Yii2Oauth2Server\Oauth2Module::class,
            // ...
            'clientRedirectUriEnvVarConfig' => [
                'allowList' => ['MY_ENV_VARS_*'], // List of patterns of which at least 1 has to match to allow replacement.
                'denyList' => [], // List of patterns of which any match will deny replacement.
                'parseNested' = false, // Should nested (a.k.a. recursive) environment variables be parsed.
            ],
        ],
        // ...
    ],
    // ...
];
```

Both the `$allowList` and `$denyList` can take 3 different types of patterns:
1. Exact match, e.g.: `'ENV_VAR_NAME'`.
2. Wildcard where `'*'` would match zero or more characters, e.g.: `'MY_ENV_VARS_*'`.
3. A regular expression, e.g.: `'/^MY_[ABC]{1,3}_VAR$/'`.

For details, please see `\rhertogh\Yii2Oauth2Server\helpers\EnvironmentHelper::parseEnvVars()`.

### Defining Environment Variables

* When configured, environment variables can be used in the format of `${ENV_VAR_NAME}`, e.g.:
  ```php
  // This would normally most likely not done in your code, but by the environment (e.g. Nginx, Apache, Docker, etc) 
  putenv('MY_APPLICATION_DOMAIN=app.my-domain.com'));
  
  $oauth2client->setRedirectUri(['https://${MY_APPLICATION_DOMAIN}/redirect_uri/']);
  $oauth2client->persist();
  ```
  > Hint: It's possible to use multiple environment variables in the same URL.

* You can also define a single environment variable that contains an array of URLs, e.g.:  
  ```php
  // This would normally most likely not done in your code, but by the environment (e.g. Nginx, Apache, Docker, etc) 
  putenv('MY_REDIRECT_URIS=["http://localhost/redirect_uri/", "https://another.host/another/redirect/uri/"]'));
  
  $oauth2client->setRedirectUri('${MY_REDIRECT_URIS}');
  $oauth2client->persist();
  ```

* Nested environment variables are also supported, e.g. `MY_APPLICATION_DOMAIN` inside `MY_REDIRECT_URIS`:
  
  > Note: To enable nested parsing set `clientRedirectUriEnvVarConfig.parseNested` to `true`.
  
  ```php
  // This would normally most likely not done in your code, but by the environment (e.g. Nginx, Apache, Docker, etc) 
  putenv('MY_REDIRECT_URIS=["https://${MY_APPLICATION_DOMAIN}/redirect_uri/", "http://localhost/redirect_uri/"]'));
  putenv('MY_APPLICATION_DOMAIN=app.my-domain.com'));
  
  $oauth2client->setRedirectUri('${MY_REDIRECT_URIS}');
  $oauth2client->persist();
  ```

## Database Storage Format

Although it's recommended to update the redirect URIs via the `\rhertogh\Yii2Oauth2Server\models\Oauth2Client` model,
it is possible to update them directly in the database.

The Oauth2Client stores the redirect URIs in `redirect_uris` as JSON, e.g.:  
A single URL as string:
```JSON
"http://localhost/redirect_uri/"
```
Multiple hosts as array:
```JSON
["http://localhost/redirect_uri/", "https://another.host/another/redirect/uri/"]
```
> Note: Since MySQL prior to version 8 treats JSON as string the data will be stored as such, e.g.:
> ```JSON
> "[\"http://localhost/redirect_uri/\"]"
> ```
