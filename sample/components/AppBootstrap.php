<?php

namespace sample\components;

use rhertogh\Yii2Oauth2Server\events\base\Oauth2BaseEvent;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Event;

class AppBootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        /**
         * Sample event handling, just logging the event for now.
         */
        Event::on(
            Oauth2Module::class,
            'Oauth2Server.*',
            function (Oauth2BaseEvent $event) {
                Yii::info($event->name, 'Oauth2 Sample Event');
            }
        );
    }
}
