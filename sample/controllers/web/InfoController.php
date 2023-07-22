<?php

namespace sample\controllers\web;

use Yii;
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
        echo 'User IP: ' . Yii::$app->request->userIP;
        echo '<br>';
        echo 'Xdebug extension loaded: ' . (extension_loaded('xdebug') ? 'yes' : 'no');
        echo '<br>';
        echo 'Xdebug debugger active: ' . ((@xdebug_is_debugger_active() ?? false) ? 'yes' : 'no');
        echo '<br>';
        echo 'realpath_cache_size: ' . Yii::$app->formatter->asShortSize(realpath_cache_size());
        echo '<br>';
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
