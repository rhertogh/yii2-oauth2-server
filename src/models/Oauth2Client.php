<?php

namespace rhertogh\Yii2Oauth2Server\models;

use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\models\behaviors\DateTimeBehavior;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2ActiveRecordIdTrait;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2EnabledTrait;
use rhertogh\Yii2Oauth2Server\models\traits\Oauth2EntityIdentifierTrait;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\UnknownPropertyException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class Oauth2Client extends base\Oauth2Client implements Oauth2ClientInterface
{
    use Oauth2ActiveRecordIdTrait;
    use Oauth2EntityIdentifierTrait;
    use Oauth2EnabledTrait;

    protected const ENCRYPTED_ATTRIBUTES = ['secret', 'old_secret'];

    /**
     * Minimum lenght for client secret.
     * @var int
     */
    public $minimumSecretLenth = 10;

    /////////////////////////////
    /// ActiveRecord Settings ///
    /////////////////////////////

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'dateTimeBehavior' => DateTimeBehavior::class
        ]);
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [
                ['secret'],
                'required',
                'when' => fn(self $model) => $model->isConfidential(),
            ],
            [
                ['scope_access'],
                'in',
                'range' => static::SCOPE_ACCESSES,
            ]
        ]);
    }

    /////////////////////////
    /// Getters & Setters ///
    /////////////////////////

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        if ($name === 'secret') { // Don't allow setting the secret via magic method.
            throw new UnknownPropertyException('For security the "secret" property must be set via setSecret()');
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function getRedirectUri()
    {
        $uri = $this->redirect_uris;
        if (is_string($uri)) {
            try {
                $uri = Json::decode($uri);
            } catch (InvalidArgumentException $e) {
                throw new InvalidConfigException('Invalid json in redirect_uris for client ' . $this->id, 0, $e);
            }
        }

        return is_array($uri) ? array_values($uri) : $uri;
    }

    /**
     * @inheritDoc
     */
    public function setRedirectUri($uri)
    {
        if (is_array($uri)) {
            foreach ($uri as $value) {
                if (!is_string($value)) {
                    throw new InvalidArgumentException('When $uri is an array, it\'s values must be strings.');
                }
            }
            $uri = Json::encode($uri);
        } elseif (is_string($uri)) {
            $uri = Json::encode([$uri]);
        } else {
            throw new InvalidArgumentException('$uri must be a string or an array, got: ' . gettype($uri));
        }

        $this->redirect_uris = $uri;
    }

    /**
     * @inheritDoc
     */
    public function getUserAccountSelection()
    {
        return $this->user_account_selection;
    }

    /**
     * @inheritDoc
     */
    public function isAuthCodeWithoutPkceAllowed()
    {
        return (bool)$this->allow_auth_code_without_pkce;
    }

    /**
     * @inheritDoc
     */
    public function skipAuthorizationIfScopeIsAllowed()
    {
        return (bool)$this->skip_authorization_if_scope_is_allowed;
    }

    /**
     * @inheritDoc
     */
    public function getClientCredentialsGrantUserId()
    {
        return $this->client_credentials_grant_user_id;
    }

    /**
     * @inheritDoc
     */
    public function getOpenIdConnectAllowOfflineAccessWithoutConsent()
    {
        return (bool)$this->oidc_allow_offline_access_without_consent;
    }

    /**
     * @inheritDoc
     */
    public function getOpenIdConnectUserinfoEncryptedResponseAlg()
    {
        return $this->oidc_userinfo_encrypted_response_alg;
    }

    /**
     * @inheritdoc
     */
    public function isConfidential()
    {
        return (int)$this->type !== static::TYPE_PUBLIC;
    }

    /**
     * @inheritDoc
     */
    public function getScopeAccess()
    {
        return (int)$this->scope_access;
    }

    /**
     * @inheritDoc
     */
    public static function getEncryptedAttributes()
    {
        return static::ENCRYPTED_ATTRIBUTES;
    }

    /**
     * @inheritDoc
     */
    public static function rotateStorageEncryptionKeys($encryptor, $newKeyName = null)
    {
        $numUpdated = 0;
        $encryptedAttributes = static::getEncryptedAttributes();
        $query = static::find()->andWhere(['NOT', array_fill_keys($encryptedAttributes, null)]);

        $transaction = static::getDb()->beginTransaction();
        try {
            /** @var static $client */
            foreach ($query->each() as $client) {
                $client->rotateStorageEncryptionKey($encryptor, $newKeyName);
                if ($client->getDirtyAttributes($encryptedAttributes)) {
                    $client->persist();
                    $numUpdated++;
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $numUpdated;
    }

    /**
     * @inheritDoc
     */
    public static function getUsedStorageEncryptionKeys($encryptor)
    {
        $encryptedAttributes = static::getEncryptedAttributes();
        $query = static::find()->andWhere(['NOT', array_fill_keys($encryptedAttributes, null)]);

        $keyUsage = [];
        foreach ($query->each() as $client) { /** @var  static $client */
            foreach ($encryptedAttributes as $encryptedAttribute) {
                $data = $client->$encryptedAttribute;
                if (!empty($data)) {
                    list('keyName' => $keyName) = $encryptor->parseData($data);
                    if (array_key_exists($keyName, $keyUsage)) {
                        $keyUsage[$keyName][] = $client->getPrimaryKey();
                    } else {
                        $keyUsage[$keyName] = [$client->getPrimaryKey()];
                    }
                }
            }
        }

        return $keyUsage;
    }

    /**
     * @inheritDoc
     */
    public function rotateStorageEncryptionKey($encryptor, $newKeyName = null)
    {
        foreach (static::getEncryptedAttributes() as $attribute) {
            $data = $this->getAttribute($attribute);
            if ($data) {
                try {
                    $this->setAttribute($attribute, $encryptor->rotateKey($data, $newKeyName));
                } catch (\Exception $e) {
                    throw new Exception('Unable to rotate key for client "' . $this->identifier
                        . '", attribute "' . $attribute . '": ' . $e->getMessage(), 0, $e);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function setSecret($secret, $encryptor, $oldSecretValidUntil = null, $keyName = null)
    {
        if ($this->isConfidential()) {
            if (!$this->validateNewSecret($secret, $error)) {
                throw new InvalidArgumentException($error);
            }

            // Ensure we clear out any old secret.
            $this->setAttribute('old_secret', null);
            $this->setAttribute('old_secret_valid_until', null);

            if ($oldSecretValidUntil) {
                $oldSecretData = $this->getAttribute('secret') ?? null;
                if ($oldSecretData) {
                    // Ensure correct encryption key.
                    $oldSecretData = $encryptor->encryp($encryptor->decrypt($oldSecretData), $keyName);
                    $this->setAttribute('old_secret', $oldSecretData);

                    if ($oldSecretValidUntil instanceof \DateInterval) {
                        $oldSecretValidUntil = (new \DateTimeImmutable())->add($oldSecretValidUntil);
                    }
                    $this->setAttribute('old_secret_valid_until', $oldSecretValidUntil);
                }
            }

            $this->setAttribute('secret', $encryptor->encryp($secret, $keyName));
        } else {
            if ($secret !== null) {
                throw new InvalidArgumentException(
                    'The secret for a non-confidential client can only be set to `null`.'
                );
            }

            $this->setAttribute('secret', null);
        }
    }

    /**
     * @inheritDoc
     */
    public function validateNewSecret($secret, &$error)
    {
        $error = null;
        if (mb_strlen($secret) < $this->minimumSecretLenth) {
            $error = 'Secret should be at least ' . $this->minimumSecretLenth . ' characters.';
        }

        return $error === null;
    }

    /**
     * @inheritDoc
     */
    public function getDecryptedSecret($encryptor)
    {
        return $encryptor->decrypt($this->secret);
    }

    /**
     * @inheritDoc
     */
    public function getDecryptedOldSecret($encryptor)
    {
        return $encryptor->decrypt($this->old_secret);
    }

    /**
     * @inheritDoc
     */
    public function getOldSecretValidUntil()
    {
        return $this->old_secret_valid_until;
    }

    /**
     * @inheritdoc
     */
    public function validateSecret($secret, $encryptor)
    {
        return is_string($secret)
            && strlen($secret)
            && (
                Yii::$app->security->compareString($this->getDecryptedSecret($encryptor), $secret)
                || (
                    !empty($this->old_secret)
                    && !empty($this->old_secret_valid_until)
                    && $this->old_secret_valid_until > (new \DateTime())
                    && Yii::$app->security->compareString($encryptor->decrypt($this->old_secret), $secret)
                )
            );
    }

    /**
     * @inheritDoc
     */
    public function getGrantTypes()
    {
        return (int)$this->grant_types;
    }

    /**
     * @inheritDoc
     */
    public function setGrantTypes($grantTypes)
    {
        $grantTypeIds = array_flip(Oauth2Module::GRANT_TYPE_MAPPING);
        for ($i = (int)log(PHP_INT_MAX, 2); $i >= 0; $i--) {
            $grantTypeId = (int)pow(2, $i);
            if ($grantTypes & $grantTypeId) {
                if (!array_key_exists($grantTypeId, $grantTypeIds)) {
                    throw new InvalidArgumentException('Unknown Grant Type ID: ' . $grantTypeId);
                }
            }
        }

        $this->grant_types = $grantTypes;
    }

    /**
     * @inheritDoc
     */
    public function validateGrantType($grantTypeIdentifier)
    {
        $grantTypeId = Oauth2Module::getGrantTypeId($grantTypeIdentifier);
        if (empty($grantTypeId)) {
            throw new InvalidArgumentException('Unknown grant type "' . $grantTypeIdentifier . '".');
        }

        return (bool)($this->getGrantTypes() & $grantTypeId);
    }

    /**
     * @inheritDoc
     */
    public function validateAuthRequestScopes($scopeIdentifiers, &$unauthorizedScopes = [])
    {
        if (
            empty($scopeIdentifiers)
            // Quiet mode will always allow the request (scopes will silently be limited to the defined ones).
            || $this->getScopeAccess() === static::SCOPE_ACCESS_STRICT_QUIET
        ) {
            $unauthorizedScopes = [];
            return true;
        }

        $allowedScopeIdentifiers = array_map(
            fn($scope) => $scope->getIdentifier(),
            $this->getAllowedScopes($scopeIdentifiers)
        );

        $unauthorizedScopes = array_values(array_diff($scopeIdentifiers, $allowedScopeIdentifiers));

        return empty($unauthorizedScopes);
    }

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function getAllowedScopes($requestedScopeIdentifiers = [])
    {
        /** @var Oauth2ClientScopeInterface $clientScopeClass */
        $clientScopeClass = DiHelper::getValidatedClassName(Oauth2ClientScopeInterface::class);
        $clientScopeTableName = $clientScopeClass::tableName();
        /** @var Oauth2ScopeInterface $scopeClass */
        $scopeClass = DiHelper::getValidatedClassName(Oauth2ScopeInterface::class);
        $scopeTableName = $scopeClass::tableName();

        $possibleScopesConditions = [
            // Default scopes defined for this client.
            ['AND',
                [$clientScopeTableName . '.client_id' => $this->getPrimaryKey()],
                [$clientScopeTableName . '.enabled' => 1],
                ['OR',
                    ...(
                        !empty($requestedScopeIdentifiers)
                            ? [[$scopeTableName . '.identifier' => $requestedScopeIdentifiers]]
                            : []
                    ),
                    ['NOT', [$clientScopeTableName . '.applied_by_default' => Oauth2Scope::APPLIED_BY_DEFAULT_NO]],
                    ['AND',
                        [$clientScopeTableName . '.applied_by_default' => null],
                        ['NOT', [$scopeTableName . '.applied_by_default' => Oauth2Scope::APPLIED_BY_DEFAULT_NO]],
                    ],
                ],
            ],
        ];

        $scopeAccess = $this->getScopeAccess();
        if ($scopeAccess === Oauth2Client::SCOPE_ACCESS_PERMISSIVE) {
            // Default scopes defined by scope for all client.
            $possibleScopesConditions[] = ['AND',
                [$clientScopeTableName . '.client_id' => null],
                ['OR',
                    ...(
                        !empty($requestedScopeIdentifiers)
                            ? [[$scopeTableName . '.identifier' => $requestedScopeIdentifiers]]
                            : []
                    ),
                    ['NOT', [$scopeTableName . '.applied_by_default' => Oauth2Scope::APPLIED_BY_DEFAULT_NO]],
                ],
            ];
        } elseif (
            ($scopeAccess !== Oauth2Client::SCOPE_ACCESS_STRICT)
            && ($scopeAccess !== Oauth2Client::SCOPE_ACCESS_STRICT_QUIET)
        ) {
            // safeguard against unknown types.
            throw new \LogicException('Unknown scope_access: "' . $scopeAccess . '".');
        }

        return $scopeClass::find()
            ->joinWith('clientScopes', true)
            ->enabled()
            ->andWhere(['OR', ...$possibleScopesConditions])
            ->all();
    }
}
