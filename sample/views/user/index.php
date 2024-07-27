<?php

use sample\models\User;
use yii\web\View;

/**
 * @var View $this
 * @var User $user
 */

if ($user) {
    echo 'Current user: ' . $user->username . ' (id: ' . $user->id . ')';
} else {
    echo 'Not logged in';
}
