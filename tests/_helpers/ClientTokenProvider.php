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




    # region PKCE https://github.com/thephpleague/oauth2-client/pull/901
    public const PKCE_METHOD_S256 = 'S256';
    protected $pkceCode;
    public $pkceMethod = null;
    public function setPkceCode($pkceCode)
    {
        $this->pkceCode = $pkceCode;
        return $this;
    }
    public function getPkceCode()
    {
        return $this->pkceCode;
    }
    protected function getRandomPkceCode($length = 64)
    {
        return substr(
            strtr(
                base64_encode(random_bytes($length)),
                '+/',
                '-_'
            ),
            0,
            $length
        );
    }
    protected function getPkceMethod()
    {
        return $this->pkceMethod;
    }
    protected function getAuthorizationParameters(array $options)
    {
        if (empty($options['state'])) {
            $options['state'] = $this->getRandomState();
        }
        if (empty($options['scope'])) {
            $options['scope'] = $this->getDefaultScopes();
        }
        $options += [
            'response_type'   => 'code',
            'approval_prompt' => 'auto'
        ];
        if (is_array($options['scope'])) {
            $separator = $this->getScopeSeparator();
            $options['scope'] = implode($separator, $options['scope']);
        }
        // Store the state as it may need to be accessed later on.
        $this->state = $options['state'];

        $pkceMethod = $this->getPkceMethod();
        if (!empty($pkceMethod)) {
            $this->pkceCode = $this->getRandomPkceCode();
            if ($pkceMethod === static::PKCE_METHOD_S256) {
                $options['code_challenge'] = trim(
                    strtr(
                        base64_encode(hash('sha256', $this->pkceCode, true)),
                        '+/',
                        '-_'
                    ),
                    '='
                );
            } elseif ($pkceMethod === static::PKCE_METHOD_PLAIN) {
                $options['code_challenge'] = $this->pkceCode;
            } else {
                throw new InvalidArgumentException('Unknown PKCE method "' . $pkceMethod . '".');
            }
            $options['code_challenge_method'] = $pkceMethod;
        }

        // Business code layer might set a different redirect_uri parameter
        // depending on the context, leave it as-is
        if (!isset($options['redirect_uri'])) {
            $options['redirect_uri'] = $this->redirectUri;
        }
        $options['client_id'] = $this->clientId;
        return $options;
    }
    public function getAccessToken($grant, array $options = [])
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
        $request  = $this->getAccessTokenRequest($params);
        $response = $this->getParsedResponse($request);
        if (false === is_array($response)) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.'
            );
        }
        $prepared = $this->prepareAccessTokenResponse($response);
        $token    = $this->createAccessToken($prepared, $grant);
        return $token;
    }
    protected function getConfigurableOptions()
    {
        return array_merge($this->getRequiredOptions(), [
            'accessTokenMethod',
            'accessTokenResourceOwnerId',
            'scopeSeparator',
            'responseError',
            'responseCode',
            'responseResourceOwnerId',
            'scopes',
            'pkceMethod',
        ]);
    }
    # endregion
}
