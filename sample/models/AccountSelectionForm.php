<?php

namespace sample\models;

use yii\base\Model;

/**
 * AccountSelectionForm is the model behind the OpenID Connect user account selection form.
 *
 * @property-read User|null $user This property is read-only.
 *
 */
class AccountSelectionForm extends Model
{
    /**
     * ID of the selected identity
     * @var int|null
     */
    public $identityId = null;

    /**
     * Helper property to access current user identity (not set via user input)
     * @var User|null
     */
    public $user = null;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['identityId', 'required'],
            ['identityId', 'integer'],
            ['identityId', function ($attribute) {
                if (!$this->user->hasLinkedIdentity($this->$attribute)) {
                    $this->addError(
                        $attribute,
                        'The current user does not have access to account ' . $this->$attribute
                    );
                }
            }],
        ];
    }
}
