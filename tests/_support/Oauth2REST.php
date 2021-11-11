<?php

namespace Yii2Oauth2ServerTests;

use Codeception\Module\REST;

class Oauth2REST extends REST
{
    /**
     * Returns the values of all http headers
     *
     * @return array Array of headers
     * @part json
     * @part xml
     */
    public function grabHttpHeaders()
    {
        return $this->getRunningClient()->getInternalResponse()->getHeaders();
    }

    /**
     * Returns the HTTP Status Code
     *
     * @return int The HTTP Status Code
     * @part json
     * @part xml
     */
    public function grabStatusCode()
    {
        return $this->getRunningClient()->getInternalResponse()->getStatusCode();
    }
}
