<?php

namespace sample\models;

use yii\base\Model;

/**
 * RegisterForm is the model behind the registration form.
 *
 */
class RegisterForm extends Model
{
    public $username;
    public $password;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['password', 'required'],
            ['password', 'string', 'min' => 5],
        ];
    }

    /**
     * @return User|null The new account when the creation was successful, null otherwise.
     */
    public function register()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email_address = $this->username . '@example.com';
        $user->setPassword($this->password);
        $user->created_at = time();
        $user->updated_at = time();

        return $user->save() ? $user : null;
    }
}
