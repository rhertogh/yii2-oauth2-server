<?php

namespace rhertogh\Yii2Oauth2Server\filters\auth;

use League\OAuth2\Server\Exception\OAuthServerException;
use rhertogh\Yii2Oauth2Server\interfaces\filters\auth\Oauth2HttpBearerAuthInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\filters\auth\HttpBearerAuth;

class Oauth2HttpBearerAuth extends HttpBearerAuth implements Oauth2HttpBearerAuthInterface
{
    public $oauth2ModuleName = null;

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get($this->header);

        if ($authHeader !== null) {
            if ($this->pattern !== null) {
                if (preg_match($this->pattern, $authHeader, $matches)) {
                    $authHeader = $matches[1];
                } else {
                    return null;
                }
            }

            /** @var Oauth2Module $module */
            $module = empty($this->oauth2ModuleName)
                ? Oauth2Module::getInstance()
                : Yii::$app->getModule($this->oauth2ModuleName);

            try {
                $module->validateAuthenticatedRequest();
            } catch (OAuthServerException $e) {
                return null;
            }

            $identity = $user->loginByAccessToken($authHeader, get_class($this));
            if ($identity === null) {
                $this->challenge($response);
                $this->handleFailure($response);
            }

            return $identity;
        }

        return null;
    }
}
