<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console;

use rhertogh\Yii2Oauth2Server\controllers\console\base\Oauth2BaseConsoleController;
use rhertogh\Yii2Oauth2Server\controllers\console\client\Oauth2CreateClientAction;
use rhertogh\Yii2Oauth2Server\controllers\console\client\Oauth2DeleteClientAction;
use rhertogh\Yii2Oauth2Server\controllers\console\client\Oauth2ListClientsAction;
use rhertogh\Yii2Oauth2Server\controllers\console\client\Oauth2SetClientSecretAction;
use rhertogh\Yii2Oauth2Server\controllers\console\client\Oauth2UpdateClientAction;
use rhertogh\Yii2Oauth2Server\controllers\console\client\Oauth2ViewClientAction;
use yii\helpers\ArrayHelper;

class Oauth2ClientController extends Oauth2BaseConsoleController
{
    /**
     * @inheritdoc
     */
    public $defaultAction = 'list';

    /**
     * @var string|null
     */
    public $sample = null;

    /**
     * @var string|null
     */
    public $identifier = null;

    /**
     * @var string|null
     */
    public $name = null;

    /**
     * @var string|null
     */
    public $type = null;

    /**
     * @var string|null
     */
    public $grantTypes = null;

    /**
     * @var string|null
     */
    public $redirectURIs = null;

    /**
     * @var string|null
     */
    public $allowVariableRedirectUriQuery = null;

    /**
     * @var string|null
     */
    public $secret = null;

    /**
     * @var string|null A PHP date/time string or, when starting with 'P', a PHP date interval string.
     * @see https://www.php.net/manual/en/datetime.formats.php
     * @see https://www.php.net/manual/en/dateinterval.construct.php
     */
    public $oldSecretValidUntil = null;

    /**
     * @var string|null
     */
    public $scopes = null;

    /**
     * @var string|null
     */
    public $allowGenericScopes = null;

    /**
     * @var string|null
     */
    public $exceptionOnInvalidScope = null;

    /**
     * @var string|null
     */
    public $endUsersMayAuthorizeClient = null;

    /**
     * @var string|null
     */
    public $userAccountSelection = null;

    /**
     * @var string|null
     */
    public $isAuthCodeWithoutPkceAllowed = null;

    /**
     * @var string|null
     */
    public $skipAuthorizationIfScopeIsAllowed = null;

    /**
     * @var string|null
     */
    public $logoUri = null;

    /**
     * @var string|null
     */
    public $termsOfServiceUri = null;

    /**
     * @var string|null
     */
    public $contacts = null;

    /**
     * @inheritDoc
     */
    public function options($actionID)
    {
        if ($actionID == 'create') {
            $options = [
                'sample',
                'identifier',
                'name',
                'type',
                'redirectURIs',
                'allowVariableRedirectUriQuery',
                'grantTypes',
                'secret',
                'scopes',
                'allowGenericScopes',
                'exceptionOnInvalidScope',
                'endUsersMayAuthorizeClient',
                'userAccountSelection',
                'isAuthCodeWithoutPkceAllowed',
                'skipAuthorizationIfScopeIsAllowed',
                'logoUri',
                'termsOfServiceUri',
                'contacts',
            ];
        } elseif ($actionID == 'set-secret') {
            $options = [
                'identifier',
                'secret',
                'oldSecretValidUntil',
            ];
        }

        return ArrayHelper::merge(parent::options($actionID), $options ?? []);
    }

    /**
     * @inheritDoc
     */
    public function actions()
    {
        return [
            'list' => Oauth2ListClientsAction::class,
            'view' => Oauth2ViewClientAction::class,
            'create' => Oauth2CreateClientAction::class,
            'update' => Oauth2UpdateClientAction::class,
            'set-secret' => Oauth2SetClientSecretAction::class,
            'delete' => Oauth2DeleteClientAction::class,
        ];
    }
}
