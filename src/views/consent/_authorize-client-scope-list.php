<?php

use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ClientAuthorizationRequestInterface;
use rhertogh\Yii2Oauth2Server\interfaces\components\authorization\Oauth2ScopeAuthorizationRequestInterface;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var Oauth2ClientAuthorizationRequestInterface $clientAuthorizationRequest
 * @var Oauth2ScopeAuthorizationRequestInterface[] $scopeRequests
 * @var ActiveForm $form
 */

foreach ($scopeRequests as $scopeRequest) : ?>
    <hr>
    <div>
        <?php
        $scope = $scopeRequest->getScope();
        $inputId = Html::getInputId($clientAuthorizationRequest, 'selectedScopeIdentifiers')
                        . '-'
                        . preg_replace('/[^a-z0-9_]/', '_', mb_strtolower($scope->getIdentifier()));

        $authorizationMessage = $scope->getAuthorizationMessage() ?? $scope->getDescription() ?? $scope->getIdentifier();

        $field = $form
            ->field($clientAuthorizationRequest, 'selectedScopeIdentifiers[]', [
                'inputOptions' => [
                    'id' => $inputId,
                ],
            ]);
        if ($scopeRequest->getIsRequired()) {
            echo $field
                ->hiddenInput(['value' => $scope->getIdentifier()])
                ->label(Html::encode($authorizationMessage));
        } else {
            echo $field
                ->checkbox([
                    'id' => $inputId,
                    'value' => $scope->getIdentifier(),
                    'checked' => !$scopeRequest->getHasBeenRejectedBefore(),
                    'label' => Html::encode($authorizationMessage),
                    'uncheck' => null,
                ]);
        }
        ?>
    </div>
<?php endforeach;
