<?php

namespace sample\controllers;

use yii\web\Controller;
use yii\web\UnauthorizedHttpException;

class InfoController extends Controller
{
    /**
     * Show phpinfo()
     * @return string
     * @throws UnauthorizedHttpException
     */
    public function actionIndex()
    {
        if (!YII_DEBUG) {
            throw new UnauthorizedHttpException();
        }

        ob_start();
        phpinfo();
        $output = ob_get_clean();

        if (extension_loaded('xdebug')) {
            ob_start();
            xdebug_info();
            $output .= ob_get_clean();
        }

        return $output;
    }
}
