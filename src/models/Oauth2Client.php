<?php

namespace rhertogh\Yii2Oauth2Server\models;

use rhertogh\Yii2Oauth2Server\helpers\DiHelper;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientScopeInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ScopeInterface;
use rhertogh\Yii2Oauth2Server\models\behaviors\DateTimeBehavior;
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
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        if (!in_array($type, Oauth2ClientInterface::TYPES)) {
            throw new InvalidArgumentException('Unknown type "' . $type . '".');
        }

        $this->type = $type;
        return $this;
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function getRedirectUri()
    {
        $uris = $this->redirect_uris;
        if (empty($uris)) {
            return [];
        }

        if (is_string($uris)) {
            try {
                $uris = Json::decode($uris);
            } catch (InvalidArgumentException $e) {
                throw new InvalidConfigException('Invalid json in redirect_uris for client ' . $this->id, 0, $e);
            }
        }

        if (is_string($uris)) {
            $uris = [$uris];
        } elseif (is_array($uris)) {
            $uris = array_values($uris);
        } else {
            throw new InvalidConfigException('`redirect_uris` must be a JSON encoded string or array of strings.');
        }

        foreach ($uris as $key => $uri) {
            if (!is_string($uri)) {
                throw new InvalidConfigException('`redirect_uris` must be a JSON encoded string or array of strings.');
            }
            preg_match_all('/\${(?<name>[a-zA-Z0-9_]+)}/', $uri, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $envVar = getenv($match['name']);
                if (strlen($envVar)) {
                    $uris[$key] = str_replace($match[0], $envVar, $uris[$key]);
                } else {
                    unset($uris[$key]);
                    break;
                }
            }
        }
        return array_values($uris); // Re-index array in case elements were removed.
    }

    /**
     * @inheritDoc
     */
    public function setRedirectUri($uri)
    {
        if (is_array($uri)) {
            foreach ($uri as $value) {
                if (!is_string($value)) {
                    throw new InvalidArgumentException('When $uri is an array, its values must be strings.');
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

    public function isVariableRedirectUriQueryAllowed()
    {
        return (bool)$this->allow_variable_redirect_uri_query;
    }
    public function setAllowVariableRedirectUriQuery($allowVariableRedirectUriQuery)
    {
        $this->allow_variable_redirect_uri_query = $allowVariableRedirectUriQuery;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUserAccountSelection()
    {
        return $this->user_account_selection;
    }

    public function endUsersMayAuthorizeClient()
    {
        return $this->end_users_may_authorize_client;
    }

    public function setEndUsersMayAuthorizeClient($endUsersMayAuthorizeClient)
    {
        $this->end_users_may_authorize_client = $endUsersMayAuthorizeClient;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setUserAccountSelection($userAccountSelectionConfig)
    {
        $this->user_account_selection = $userAccountSelectionConfig;
        return $this;
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
    public function setAllowAuthCodeWithoutPkce($allowAuthCodeWithoutPkce)
    {
        $this->allow_auth_code_without_pkce = $allowAuthCodeWithoutPkce;
        return $this;
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
    public function setSkipAuthorizationIfScopeIsAllowed($skipAuthIfScopeIsAllowed)
    {
        $this->skip_authorization_if_scope_is_allowed = $skipAuthIfScopeIsAllowed;
        return $this;
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
    public function setClientCredentialsGrantUserId($userId)
    {
        $this->client_credentials_grant_user_id = $userId;
        return $this;
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
    public function setOpenIdConnectAllowOfflineAccessWithoutConsent($allowOfflineAccessWithoutConsent)
    {
        $this->oidc_allow_offline_access_without_consent = $allowOfflineAccessWithoutConsent;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOpenIdConnectUserinfoEncryptedResponseAlg()
    {
        return $this->oidc_userinfo_encrypted_response_alg;
    }

    /**
     * @inheritDoc
     */
    public function setOpenIdConnectUserinfoEncryptedResponseAlg($algorithm)
    {
        $this->oidc_userinfo_encrypted_response_alg = $algorithm;
        return $this;
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
    public function setScopeAccess($scopeAccess)
    {
        if (!in_array($scopeAccess, Oauth2ClientInterface::SCOPE_ACCESSES)) {
            throw new InvalidArgumentException('Unknown scope access "' . $scopeAccess . '".');
        }

        $this->scope_access = $scopeAccess;
        return $this;
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
        foreach ($query->each() as $client) {
            foreach ($encryptedAttributes as $encryptedAttribute) {
                $data = $client->$encryptedAttribute;
                if (!empty($data)) {
                    ['keyName' => $keyName] = $encryptor->parseData($data);
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
     * @inheritdoc
     */
    public function getLogoUri()
    {
        return $this->logo_uri;
    }

    /**
     * @inheritdoc
     */
    public function setLogoUri($logoUri)
    {
        $this->logo_uri = $logoUri;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTermsOfServiceUri()
    {
        return $this->tos_uri;
    }

    /**
     * @inheritdoc
     */
    public function setTermsOfServiceUri($tosUri)
    {
        $this->tos_uri = $tosUri;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @inheritdoc
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;
        return $this;
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
                    ['NOT', [$clientScopeTableName . '.applied_by_default' => Oauth2ScopeInterface::APPLIED_BY_DEFAULT_NO]],
                    ['AND',
                        [$clientScopeTableName . '.applied_by_default' => null],
                        ['NOT', [$scopeTableName . '.applied_by_default' => Oauth2ScopeInterface::APPLIED_BY_DEFAULT_NO]],
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
                    ['NOT', [$scopeTableName . '.applied_by_default' => Oauth2ScopeInterface::APPLIED_BY_DEFAULT_NO]],
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
            ->orderBy('id')
            ->all();
    }

    /**
     * @inheritdoc
     * @return array{
     *     'unaffected': Oauth2ClientScopeInterface[],
     *     'new': Oauth2ClientScopeInterface[],
     *     'updated': Oauth2ClientScopeInterface[],
     *     'deleted': Oauth2ClientScopeInterface[],
     * }
     */
    public function syncClientScopes($scopes, $scopeRepository)
    {
        if (is_string($scopes)) {
            $scopes = explode(' ', $scopes);
        } elseif ($scopes === null) {
            $scopes = [];
        } elseif (!is_array($scopes)) {
            throw new InvalidArgumentException('$scopes must be a string, an array or null.');
        }

        /** @var class-string<Oauth2ClientScopeInterface> $clientScopeClass */
        $clientScopeClass = DiHelper::getValidatedClassName(Oauth2ClientScopeInterface::class);

        /** @var Oauth2ClientScopeInterface[] $origClientScopes */
        $origClientScopes = $clientScopeClass::findAll([
            'client_id' => $this->getPrimaryKey(),
        ]);

        $origClientScopes = array_combine(
            array_map(
                function(Oauth2ClientScopeInterface $clientScope) {
                    return implode('-', $clientScope->getPrimaryKey(true));
                },
                $origClientScopes
            ),
            $origClientScopes
        );

        /** @var Oauth2ClientScopeInterface[] $clientScopes */
        $clientScopes = [];

        foreach ($scopes as $key => $value) {
            if ($value instanceof Oauth2ClientScopeInterface) {
                $clientScope = $value;
                $clientScope->client_id = $this->getPrimaryKey(); // Ensure PK is set
                $pkIndex = implode('-', $clientScope->getPrimaryKey(true));
                if (array_key_exists($pkIndex, $origClientScopes)) {
                    // overwrite orig (might still be considered "unchanged" when new ClientScope is not "dirty").
                    $origClientScopes[$pkIndex] = $clientScope;
                }
            } else {

                $scopeIdentifier = null;
                $clientScopeConfig = [
                    'client_id' => $this->getPrimaryKey(),
                ];

                if (is_string($value)) {
                    $scopeIdentifier = $value;
                } elseif ($value instanceof Oauth2ScopeInterface) {
                    $scopePk = $value->getPrimaryKey();
                    if ($scopePk) {
                        $clientScopeConfig = ArrayHelper::merge(
                            $clientScopeConfig,
                            ['scope_id' => $scopePk]
                        );
                    } else {
                        // new model, using identifier
                        $scopeIdentifier = $value->getIdentifier();
                    }
                } elseif (is_array($value)) {
                    $clientScopeConfig = ArrayHelper::merge(
                        $clientScopeConfig,
                        $value,
                    );
                    if (empty($clientScopeConfig['scope_id'])) {
                        $scopeIdentifier = $key;
                    }
                } else {
                    throw new InvalidArgumentException(
                        'If $scopes is an array, its values must be a string, array or an instance of '
                        . Oauth2ClientScopeInterface::class . ' or ' . Oauth2ScopeInterface::class . '.'
                    );
                }

                if (isset($scopeIdentifier)) {
                    $scope = $scopeRepository->findModelByIdentifier($scopeIdentifier);
                    if (empty($scope)) {
                        throw new InvalidArgumentException('No scope with identifier "'
                            . $scopeIdentifier . '" found.');
                    }
                    $clientScopeConfig['scope_id'] = $scope->getPrimaryKey();
                } else {
                    if (empty($clientScopeConfig['scope_id'])) {
                        throw new InvalidArgumentException('Element ' . $key . ' in $scope should specify either the scope id or its identifier.');
                    }
                }

                $pkIndex = $clientScopeConfig['client_id'] . '-' . $clientScopeConfig['scope_id'];
                if (array_key_exists($pkIndex, $origClientScopes)) {
                    $clientScope = $origClientScopes[$pkIndex];
                    $clientScope->setAttributes($clientScopeConfig, false);
                } else {
                    /** @var Oauth2ClientScopeInterface $clientScope */
                    $clientScope = Yii::createObject(ArrayHelper::merge(
                        ['class' => $clientScopeClass],
                        $clientScopeConfig
                    ));
                }
            }

            $pkIndex = implode('-', $clientScope->getPrimaryKey(true));
            $clientScopes[$pkIndex] = $clientScope;
        }

        $transaction = static::getDb()->beginTransaction();
        try {
            //Delete records no longer present in the provided data
            /** @var self[]|array[] $deleteClientScopes */
            $deleteClientScopes = array_diff_key($origClientScopes, $clientScopes);
            foreach ($deleteClientScopes as $deleteClientScope) {
                $deleteClientScope->delete();
            }

            //Create records not present in the provided data
            $createClientScopes = array_diff_key($clientScopes, $origClientScopes);
            foreach ($createClientScopes as $createClientScope) {
                $createClientScope->persist();
            }

            //Update existing records if needed
            $unaffectedClientScopes = [];
            $updatedClientScopes = [];
            foreach (array_intersect_key($origClientScopes, $clientScopes) as $key => $existingClientScope) {
                if ($existingClientScope->getDirtyAttributes()) {
                    $existingClientScope->persist();
                    $updatedClientScopes[$key] = $existingClientScope;
                } else {
                    $unaffectedClientScopes[$key] = $existingClientScope;
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return [
            'unaffected' => $unaffectedClientScopes,
            'new' => $createClientScopes,
            'updated' => $updatedClientScopes,
            'deleted' => $deleteClientScopes,
        ];
    }
}
