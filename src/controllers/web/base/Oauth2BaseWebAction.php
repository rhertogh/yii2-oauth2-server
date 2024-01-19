<?php

namespace rhertogh\Yii2Oauth2Server\controllers\web\base;

use yii\base\Action;
use yii\web\Request;

class Oauth2BaseWebAction extends Action
{
    /**
     * @param Request $request
     * @param string $name
     * @return mixed
     */
    protected function getRequestParam($request, $name, $defaultValue = null)
    {
        return $request->post($name) ?? $request->get($name, $defaultValue);
    }
}
