<?php

namespace sample\models;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2OidcUserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2PasswordGrantUserInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2UserInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use rhertogh\Yii2Oauth2Server\traits\models\Oauth2OidcUserIdentityTrait;
use rhertogh\Yii2Oauth2Server\traits\models\Oauth2UserIdentityTrait;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property string $username
 * @property string $password_hash
 * @property string $email_address
 * @property string $latest_authenticated_at
 * @property boolean $enabled
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read User[] $linkedIdentities
 */
class User extends ActiveRecord implements
    IdentityInterface,
    Oauth2UserInterface,
    Oauth2OidcUserInterface, # Optional interface, only required when OpenID Connect support is enabled
    Oauth2PasswordGrantUserInterface # Optional interface, only required when `password` grant type is used
{
    use Oauth2UserIdentityTrait; # Helper trait for Oauth2UserInterface
    use Oauth2OidcUserIdentityTrait; # Optional helper trait for Oauth2OidcUserInterface, only required when OpenID Connect support is enabled

    # region IdentityInterface (Default Yii interface)
    /**
     * @inheritDoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
    }

    /**
     * @inheritDoc
     */
    public function validateAuthKey($authKey)
    {
        // TODO: Implement getAuthKey() method.
    }
    # endregion IdentityInterface

    # region Oauth2UserInterface
    # Methods are implemented via Oauth2UserIdentityTrait
    # endregion Oauth2UserInterface

    # region Oauth2OidcUserInterface
    /**
     * @inheritDoc
     */
    public function getLatestAuthenticatedAt()
    {
        return new \DateTimeImmutable('@' . ($this->latest_authenticated_at ?? $this->created_at));
    }

    # Other methods are implemented via Oauth2OidcUserIdentityTrait
    # endregion Oauth2OidcUserInterface

    # region Oauth2PasswordGrantUserInterface (Optional interface, only required when `password` grant type is used)
    /**
     * @inheritDoc
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * @inheritDoc
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * @inheritDoc
     */
    public function canAccessOauth2ClientAndGrantType(ClientEntityInterface $clientEntity, $grantType)
    {
        // Just always allow access in the sample app. For your application you might limit the access to certain clients.
        return true;
    }
    # endregion Oauth2PasswordGrantUserInterface

    # region Not Oauth specific but can be used with Oauth Scopes to hide/expose certain fields
    /**
     * @inheritDoc
     */
    public function fields()
    {
        /** @var Oauth2Module $oauth2Module */
        $oauth2Module = Yii::$app->getModule('oauth2');
        return [
            'id',
            ...($oauth2Module->requestHasScope('user.username.read') ? ['username'] : []),
            ...($oauth2Module->requestHasScope('user.email_address.read') ? ['email_address'] : []),
        ];
    }
    # endregion

    # region Application specific implementation for user identity selection
    /**
     * Checks if this user has another identity linked to their account
     * @param int $id
     * @return bool
     */
    public function hasLinkedIdentity($id)
    {
        if ($id == $this->id) {
            return true;
        }
        return $this->getUserIdentityLinks()
            ->andWhere(['linked_user_id' => $id])
            ->exists();
    }

    /**
     * Get an identity that is linked to this account
     * @param $id
     * @return User|null
     */
    public function getLinkedIdentity($id)
    {
        if ($id == $this->id) {
            return $this;
        }

        return $this->getLinkedIdentities()
            ->andWhere(['id' => $id])
            ->one();
    }

    /**
     * Get all available identities for this account (including itself)
     * @return User[]
     */
    public function getAvailableIdentities()
    {
        return [$this, ...$this->linkedIdentities];
    }
    # endregion

    # region ActiveRecord Relations
    /**
     * @return ActiveQuery
     */
    public function getUserIdentityLinks()
    {
        return $this->hasMany(UserIdentityLink::class, ['user_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getLinkedIdentities()
    {
        return $this->hasMany(User::class, ['id' => 'linked_user_id'])->via('userIdentityLinks');
    }
    # endregion
}
