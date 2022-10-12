<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console;

use rhertogh\Yii2Oauth2Server\controllers\console\base\Oauth2BaseConsoleController;
use rhertogh\Yii2Oauth2Server\controllers\console\PersonalAccessToken\Oauth2GeneratePatAction;
use yii\helpers\ArrayHelper;

class Oauth2PersonalAccessTokenController extends Oauth2BaseConsoleController
{
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
