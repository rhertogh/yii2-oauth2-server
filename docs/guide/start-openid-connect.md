OpenID Connect for the Yii2-Oauth2-Server
=========================================

OpenID Connect (OIDC) is used for authentication on top of OAuth 2.0 (which is used for authorization).

Setup
-----
OIDC is optional and is disabled by default, you can enable it by setting `enableOpenIdConnect` to `true`
for the Oauth2Module in your app config. E.g.:
 ```php
return [
    // ...
    'modules' => [
        'oauth2' => [
            'class' => rhertogh\Yii2Oauth2Server\Oauth2Module::class,
            // ...
            'enableOpenIdConnect' => true,
        ],
        // ...
    ],
    // ...
];
```
> Note: If you enable OIDC later on you might need to regenerate and rerun the migration commands again.

### User Identity Class

In order to support OIDC your User Identity Class (a.k.a. the User Model) must implement 
`rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserInterface`

```php
<?php
namespace app\models;
//...
use yii\web\IdentityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\external\user\Oauth2OidcUserInterface;
use rhertogh\Yii2Oauth2Server\traits\models\Oauth2OidcUserIdentityTrait;
use rhertogh\Yii2Oauth2Server\traits\models\Oauth2UserIdentityTrait;
//...

class User extends ActiveRecord implements 
    IdentityInterface,
    Oauth2OidcUserInterface # Replaces the `Oauth2UserInterface`
{
    use Oauth2UserIdentityTrait; # Helper trait for Oauth2UserInterface 
    use Oauth2OidcUserIdentityTrait; # You can use the trait or implement the Oauth2UserInterface yourself.
    
    // ...
    # region Oauth2OidcUserInterface
    /**
     * @inheritDoc
     */
    public function getLatestAuthenticatedAt()
    {
        return new \DateTimeImmutable('@' . ($this->latest_authenticated_at ?: $this->created_at));
    }

    # Other methods are implemented via Oauth2OidcUserIdentityTrait
    # endregion Oauth2OidcUserInterface
}
```

### User Component

OIDC clients might request to reauthenticate users or prompt them for user account selection.  
In order to support these requests the User Component must implement
`\rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\user\Oauth2OidcUserComponentInterface`

Similar to the default `\yii\web\User::loginRequired()` this interface defines `reauthenticationRequired()`
and `accountSelectionRequired()`, both methods must return a `\yii\web\Response`.
The implementation is application specific but most likely you want to redirect the user to the default login page
with additional parameters in case of the `reauthenticationRequired()` method and force reauthentication there.
For the `accountSelectionRequired()` your application must support switching between different account, if this
is not the case you can simply return `false`. If your application does support multiple account,
the Response should redirect the user to the account selection screen.

A sample implementation might be:
```php
<?php
namespace app\components;

use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\user\Oauth2OidcUserComponentInterface;
use Yii;
use yii\web\User;

class UserComponent extends User implements Oauth2OidcUserComponentInterface
{
    /**
     * @inheritDoc
     */
    public function reauthenticationRequired($clientAuthorizationRequest)
    {
        return Yii::$app->response->redirect([
            'user/login',
            'reauthenticate' => true,
            'clientAuthorizationRequestId' => $clientAuthorizationRequest->getRequestId(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function accountSelectionRequired($clientAuthorizationRequest)
    {
        # If account selection is not supported your app should return `false`.
        return Yii::$app->response->redirect([
            'user/account-selection',
            'clientAuthorizationRequestId' => $clientAuthorizationRequest->getRequestId(),
        ]);
    }
}
```

In case of an implementation of the UserComponent as described above your UserController might look something like:

```php
<?php
namespace app\controllers;

use rhertogh\Yii2Oauth2Server\Oauth2Module;
use app\models\AccountSelectionForm;
use app\models\LoginForm;
use app\models\User;
use Yii;
use yii\web\Controller;

class UserController extends Controller
{
    public function actionLogin($reauthenticate = false, $clientAuthorizationRequestId = null)
    {
        if (!Yii::$app->user->isGuest && !$reauthenticate) {
            return $this->goBack();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            if ($clientAuthorizationRequestId) {
                Oauth2Module::getInstance()->setUserAuthenticatedDuringClientAuthRequest($clientAuthorizationRequestId, true);
            }
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionSelectAccount($clientAuthorizationRequestId)
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $model = new AccountSelectionForm([
            'user' => $user,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Oauth2Module::getInstance()->setClientAuthRequestUserIdentity(
                $clientAuthorizationRequestId,
                $user->getLinkedIdentity($model->identityId)
            );
            return $this->goBack();
        }

        return $this->render('select-account', [
            'model' => $model,
        ]);
    }
}
```

OpenID Connect Claims
---------------------
By default, the Yii2-Oauth2-Server comes pre-configured with the standard OIDC scopes and claims.  
For customization of the scopes/claims please see [OpenID Connect Claims](start-openid-connect-claims.md)


OpenID Connect and Oauth2 Refresh Tokens
----------------------------------------
In order for a client to obtain an Oauth2 Refresh Token OpenID Connect the client should include the 'offline_access' scope.  
Please see https://openid.net/specs/openid-connect-core-1_0.html#OfflineAccess for more details.

This behavior can be overridden by setting the `openIdConnectIssueRefreshTokenWithoutOfflineAccessScope` to `true`.

> Warning: Enabling this setting might introduce privacy concerns since the client could poll for the online status of a user.

 ```php
return [
    // ...
    'modules' => [
        'oauth2' => [
            'class' => rhertogh\Yii2Oauth2Server\Oauth2Module::class,
            // ...
            'enableOpenIdConnect' => true,
            'openIdConnectIssueRefreshTokenWithoutOfflineAccessScope' => true, // Warning: Enabling this setting might introduce privacy concerns.
            'grantTypes'                                              => [
                Oauth2Module::GRANT_TYPE_AUTH_CODE,
                Oauth2Module::GRANT_TYPE_REFRESH_TOKEN,
            ],
        ],
        // ...
    ],
    // ...
];
```

And implement the `Oauth2OidcUserSessionStatusInterface` on your User Identity Class (a.k.a. the User Model).
```php
<?php
namespace app\models;
//...
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserInterface;
//...

class User extends ActiveRecord implements 
    // ...
    Oauth2UserInterface
{
    // ...
    
    public function hasActiveSession(): bool
    {
        // Implement your own logic here based on the user session, or always return `true` in case refresh tokens
        // should be usable even if the OIDC scope 'offline_access' is not requested.
        return true;
    }
}
```

OpenID Connect Discovery
------------------------
The Yii2Oauth2Server support the [OpenID Provider Configuration Information](https://openid.net/specs/openid-connect-discovery-1_0.html#rfc.section.4) 
endpoint which provides the [OpenID Provider Metadata](https://openid.net/specs/openid-connect-discovery-1_0.html#rfc.section.3).
By default, this is located at `/.well-known/openid-configuration`.  

In case you want to disable this you can set `enableOpenIdConnectDiscovery` to `false`:
 ```php
return [
    // ...
    'modules' => [
        'oauth2' => [
            'class' => rhertogh\Yii2Oauth2Server\Oauth2Module::class,
            // ...
            'enableOpenIdConnectDiscovery' => false,
        ],
        // ...
    ],
    // ...
];
```
