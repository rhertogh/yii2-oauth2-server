<?php

use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\client\Oauth2ClientAuthorizationRequestInterface;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var View $this
 * @var Oauth2ClientAuthorizationRequestInterface $clientAuthorizationRequest
 */


// Note: using long class names to avoid any conflict.
?>
<style>
    .oauth2_authorize-client-wrapper {
        display: inline-flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;

        width: 100%;
        height: calc(100vh - 100px);
    }

    .oauth2_authorize-client-modal {
        background: white;
        border-radius: 3px;
        border: 1px solid rgba(0, 0, 0, 0.5);
        box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
        width: 500px;
    }

    .oauth2_authorize-client-content {
        margin: 10px;
    }

    .oauth2_authorize-client-modal-title {
        text-align: center;
        margin-top: 5px;
    }

    .oauth2_authorize-client-requested-scopes,
    .oauth2_authorize-client-previously-approved-scopes {
        margin: 15px;
    }

    .oauth2_authorize-client-modal-footer {
        display: inline-flex;
        flex-direction: row;
        justify-content: flex-end;
        gap: 20px;
        width: 100%;
        padding: 10px 25px;
    }

    .oauth2_authorize-client-modal-button {
        font: inherit;
        cursor: pointer;
        display: inline-block;
        text-align: center;
        text-decoration: none;
        margin: 2px 0;
        border: solid 2px transparent;
        border-radius: 3px;
        padding: 7px 25px;
        color: #ffffff;
        background-color: #aaaaaa;
    }

    .oauth2_authorize-client-modal-button:active {
        transform: translateY(1px);
        filter: saturate(150%);
    }

    .oauth2_authorize-client-modal-button:hover {
        background-color: #999999;
    }

    .oauth2_authorize-client-modal-button-primary {
        background-color: #227700;
    }

    .oauth2_authorize-client-modal-button-primary:hover {
        background-color: #226600;
    }

    #oauth2_authorize-client-previously-approved-scopes-toggle.open span {
        display: inline-block;
        transform: rotate(-180deg);
    }

</style>
<?php
    $approvalPendingScopes = $clientAuthorizationRequest->getApprovalPendingScopes();
    $previouslyApprovedScopes = $clientAuthorizationRequest->getPreviouslyApprovedScopes();
?>
<div class="oauth2_authorize-client-wrapper">
    <div class="oauth2_authorize-client-modal">
        <?php $form = ActiveForm::begin(['id' => 'oauth2-client-authorization-request-form']) ?>
        <div class="oauth2_authorize-client-content">
        <?php if ($approvalPendingScopes) : ?>
            <h3 class="oauth2_authorize-client-modal-title oauth2_authorize-client-modal-title_requests-scopes">
                <?= Yii::t('oauth2', '{clientName} would like to:', [
                    'clientName' => Html::encode($clientAuthorizationRequest->getClient()->getName()),
                ]); ?>
            </h3>
            <div class="oauth2_authorize-client-requested-scopes">
                <?= $this->render('_authorize-client-scope-list', [
                    'form' => $form,
                    'clientAuthorizationRequest' => $clientAuthorizationRequest,
                    'scopeRequests' => $approvalPendingScopes,
                ]); ?>
                <hr>
            </div>
            <?php if ($previouslyApprovedScopes) : ?>
                <div class="oauth2_authorize-client-previously-approved-scopes">
                    <a href="#" id="oauth2_authorize-client-previously-approved-scopes-toggle">
                        <?= Yii::t('oauth2', 'Previously accepted access:') ?>
                        <span>&#9660;</span>
                    </a>
                    <div id="oauth2_authorize-client-previously-approved-scopes-list" style="display: none">
                        <?= $this->render('_authorize-client-scope-list', [
                            'form' => $form,
                            'clientAuthorizationRequest' => $clientAuthorizationRequest,
                            'scopeRequests' => $previouslyApprovedScopes,
                        ]); ?>
                        <hr>
                    </div>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <h3 class="oauth2_authorize-client-modal-title oauth2_authorize-client-modal-title_no-scopes">
                <?= Yii::t('oauth2', '{clientName} would like to access {appName} on your behalf.', [
                    'clientName' => Html::encode($clientAuthorizationRequest->getClient()->getName()),
                    'appName' => Yii::$app->name,
                ]) ?>
            </h3>
        <?php endif; ?>
        </div>
        <div class="oauth2_authorize-client-modal-footer">
            <button
                type="submit"
                name="<?= Html::getInputName($clientAuthorizationRequest, 'authorizationStatus') ?>"
                value="<?= Oauth2ClientAuthorizationRequestInterface::AUTHORIZATION_DENIED ?>"
                class="oauth2_authorize-client-modal-button"
            >
                <?= Yii::t('oauth2', 'Cancel') ?>
            </button>
            <button
                type="submit"
                name="<?= Html::getInputName($clientAuthorizationRequest, 'authorizationStatus') ?>"
                value="<?= Oauth2ClientAuthorizationRequestInterface::AUTHORIZATION_APPROVED ?>"
                class="oauth2_authorize-client-modal-button oauth2_authorize-client-modal-button-primary"
            >
                <?= Yii::t('oauth2', 'Allow') ?>
            </button>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>

<?php
$js = <<<'JS'
    $('#oauth2_authorize-client-previously-approved-scopes-toggle').click(function(e) {
        $('#oauth2_authorize-client-previously-approved-scopes-list').slideToggle();
        $(this).toggleClass('open');
        e.preventDefault();
    });
    JS;

$this->registerJs($js);
