<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console;

use rhertogh\Yii2Oauth2Server\controllers\console\base\Oauth2BaseConsoleController;
use rhertogh\Yii2Oauth2Server\controllers\console\PersonalAccessToken\Oauth2GeneratePatAction;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\console\Oauth2PersonalAccessTokenControllerInterface;
use yii\helpers\ArrayHelper;

/**
 * Manage Oauth2 "Personal Access Tokens".
 */
class Oauth2PersonalAccessTokenController extends Oauth2BaseConsoleController implements
    Oauth2PersonalAccessTokenControllerInterface
{
    /**
     * @var string|null
     */
    public $defaultClientIdentifier = null;

    /**
     * @var string|null
     */
    public $defaultClientSecret = null;

    /**
     * @var int|string|null
     */
    public $defaultUserId = null;

    /**
     * @var string|null
     */
    public $defaultScope = null;

    /**
     * @var string|null
     */
    public $client = null;

    /**
     * @var string|null
     */
    public $clientSecret = null;

    /**
     * @var string|null
     */
    public $user = null;

    /**
     * @var string|null
     */
    public $scope = null;

    /**
     * @inheritDoc
     */
    public function options($actionID)
    {
        if ($actionID == 'generate') {
            $options = [
                'client',
                'clientSecret',
                'user',
                'scope',
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
            'generate' => Oauth2GeneratePatAction::class,
        ];
    }
}
