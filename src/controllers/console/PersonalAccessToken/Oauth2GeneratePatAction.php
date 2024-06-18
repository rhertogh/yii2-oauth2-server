<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\PersonalAccessToken;

use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2PersonalAccessTokenController;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\console\PersonalAccessToken\Oauth2GeneratePatActionInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\base\Action;
use yii\console\ExitCode;

/**
 * @property Oauth2PersonalAccessTokenController $controller
 */
class Oauth2GeneratePatAction extends Action implements Oauth2GeneratePatActionInterface
{
    public function run()
    {
        $module = $this->controller->module;

        $clientIdentifier = $this->controller->client;
        $clientSecret = $this->controller->clientSecret;
        $userId = $this->controller->user;
        $scope = $this->controller->scope;

        if (empty($clientIdentifier)) {
            $clientIdentifier = $this->controller->prompt('Client Identifier?', [
                'required' => true,
                'default' => $this->controller->defaultClientIdentifier ?? null,
                'validator' => function ($input, &$error) {
                    $client = $this->getClient($input);
                    if ($client) {
                        if (!$client->validateGrantType(Oauth2Module::GRANT_TYPE_IDENTIFIER_PERSONAL_ACCESS_TOKEN)) {
                            $error = 'The "Personal Access Token" grant type is not enabled for client "'
                                . $input . '".';
                            return false;
                        }
                    } else {
                        $error = 'No client with identifier "' . $input . '" found.';
                        return false;
                    }
                    return true;
                },
            ]);
        }

        $client = $this->getClient($clientIdentifier);

        if (empty($clientSecret) && $client->isConfidential()) {
            $clientSecret = $this->controller->prompt('Client Secret?', [
                'required' => true,
                'default' => $this->controller->defaultClientSecret ?? null,
            ]);
        }

        if ($clientSecret === 'true') {
            $clientSecret = true;
        }

        if (empty($userId)) {
            $userId = $this->controller->prompt('User Identifier?', [
                'required' => true,
                'default' => $this->controller->defaultUserId ?? null,
                'validator' => function ($input, &$error) {
                    $user = $this->getUser($input);
                    if (!$user) {
                        $error = 'No user with identifier "' . $input . '" found.';
                        return false;
                    }
                    return true;
                },
            ]);
        }

        if (empty($scope)) {
            $scope = $this->controller->prompt('Scope?', [
                'required' => false,
                'default' => $this->controller->defaultScope ?? null,
            ]);
        }

        $result = $module->generatePersonalAccessToken(
            $clientIdentifier,
            $userId,
            $scope,
            $clientSecret,
        );

        foreach ($result as $key => $value) {
            $this->controller->stdout($key . ': ' . $value . PHP_EOL);
        }

        return ExitCode::OK;
    }

    protected function getClient($identifier)
    {
        return $this->controller->module->getClientRepository()->getClientEntity($identifier);
    }

    protected function getUser($identifier)
    {
        return $this->controller->module->getUserRepository()->getUserEntityByIdentifier($identifier);
    }
}
