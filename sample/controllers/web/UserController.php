<?php

namespace sample\controllers\web;

use rhertogh\Yii2Oauth2Server\Oauth2Module;
use sample\models\AccountSelectionForm;
use sample\models\LoginForm;
use sample\models\RegisterForm;
use sample\models\User;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;

class UserController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index', [
            'user' => Yii::$app->user->identity
        ]);
    }

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

    # region Sample register action.
    /**
     * @return \yii\web\Response|array|string
     */
    public function actionRegister()
    {
        $model = new RegisterForm();
        if ($model->load(Yii::$app->request->post()))
        {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }

            $user = $model->register();
            if ($user) {
                Yii::$app->user->login($user);
                return $this->goBack();
            }
        }

        return $this->render('register', [
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
