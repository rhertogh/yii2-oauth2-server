<?php

namespace Yii2Oauth2ServerTests\_helpers;

use yii\web\Response;

class NoHeadersResponse extends Response
{
    protected function sendHeaders()
    {
        // Don't send headers in test cases.
    }
}
