<?php

namespace Yii2Oauth2ServerTests\_helpers;

use League\OAuth2\Client\Provider\GenericProvider;

class ClientTokenProvider extends GenericProvider
{

    public function getAccessTokenRequestWrapper($grant, array $options = [])
    {
        $grant = $this->verifyGrant($grant);

        $params = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
        ];

        if (!empty($this->pkceCode)) {
            $params['code_verifier'] = $this->pkceCode;
        }

        $params   = $grant->prepareRequestParameters($params, $options);
        return $this->getAccessTokenRequest($params);
    }
}
