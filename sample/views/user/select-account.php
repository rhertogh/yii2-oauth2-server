<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $model sample\models\AccountSelectionForm */

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

$this->title = 'Select Account';
$this->params['breadcrumbs'][] = $this->title;

$availableIdentities = [];
foreach ($model->user->getAvailableIdentities() as $availableIdentity) {
    $availableIdentities[$availableIdentity->id] = $availableIdentity->username;
}

?>
<div class="site-select-account">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please select an Account:</p>

    <?php $form = ActiveForm::begin([
        'id' => 'account-selection-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 col-form-label'],
        ],
    ]); ?>

    <?= $form->field($model, 'identityId')->radioList($availableIdentities)->label(false) ?>

    <div class="form-group">
        <div class="offset-lg-1 col-lg-11">
            <?= Html::submitButton('Select', ['class' => 'btn btn-primary', 'name' => 'select-account-button']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
