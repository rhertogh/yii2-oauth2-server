<?php

namespace sample\controllers;

use rhertogh\Yii2Oauth2Server\Oauth2Module;
use sample\models\AccountSelectionForm;
use sample\models\LoginForm;
use sample\models\User;
use Yii;
use yii\web\Controller;

class UserController extends Controller
{
    # region Default Yii login action with added support for OpenID Connect reauthentication
    /**
     * Allow the user to login
     * @param bool $reauthenticate
     * @param string|null $clientAuthorizationRequestId
     * @return string|\yii\web\Response
     */
    public function actionLogin($reauthenticate = false, $clientAuthorizationRequestId = null)
    {
        if (!Yii::$app->user->isGuest && !$reauthenticate) {
            return $this->goBack();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            if ($clientAuthorizationRequestId) {
                Oauth2Module::getInstance()->setUserAuthenticatedDuringClientAuthRequest(
                    $clientAuthorizationRequestId,
                    true
                );
            }
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }
    # endregion

    # region Action to support OpenID Connect account selection
    /**
     * Allow the user to select an identity
     * @param string $clientAuthorizationRequestId
     * @return string|\yii\web\Response
     */
    public function actionSelectAccount($clientAuthorizationRequestId)
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $model = new AccountSelectionForm([
            'user' => $user,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Oauth2Module::getInstance()->setClientAuthRequestUserIdentity(
                $clientAuthorizationRequestId,
                $user->getLinkedIdentity($model->identityId)
            );

            return $this->goBack();
        }

        return $this->render('select-account', [
            'model' => $model,
        ]);
    }
    # endregion
}
