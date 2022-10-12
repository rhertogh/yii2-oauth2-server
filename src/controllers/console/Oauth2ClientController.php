<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console;

use rhertogh\Yii2Oauth2Server\controllers\console\base\Oauth2BaseConsoleController;
use rhertogh\Yii2Oauth2Server\controllers\console\client\Oauth2CreateClientAction;
use rhertogh\Yii2Oauth2Server\controllers\console\client\Oauth2SetClientSecretAction;
use yii\helpers\ArrayHelper;

class Oauth2ClientController extends Oauth2BaseConsoleController
{
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
    public $secret = null;

    /**
     * @var string|null
     */
    public $scopes = null;

    /**
     * @var string|null
     */
    public $endUsersMayAuthorizeClient = null;

    /**
     * @var string|null A PHP date/time string or, when starting with 'P', a PHP date interval string.
     * @see https://www.php.net/manual/en/datetime.formats.php
     * @see https://www.php.net/manual/en/dateinterval.construct.php
     */
    public $oldSecretValidUntil = null;

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
                'grantTypes',
                'secret',
                'scopes',
                'endUsersMayAuthorizeClient',
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
            'create' => Oauth2CreateClientAction::class,
            'set-secret' => Oauth2SetClientSecretAction::class,
        ];
    }
}
