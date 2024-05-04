<?php

namespace rhertogh\Yii2Oauth2Server\helpers;

use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\ResponseTypes\RedirectResponse;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use yii\web\Request;

class Oauth2RequestHelper
{
    /**
     * @param Request $request
     * @return array
     */
    public static function getClientCredentials(Request $request)
    {
        // Piggyback on underlying Oauth2 server in order to maintain same client identification methods.
        $grant = new class extends AbstractGrant
        {
            public function getIdentifier() {
                return 'dummy';
            }

            public function respondToAccessTokenRequest(ServerRequestInterface $request, ResponseTypeInterface $responseType, \DateInterval $accessTokenTTL)
            {
                return new RedirectResponse();
            }

            /**
             * @param Request $request
             * @return array
             */
            public function getClientCredentialsFromYiiRequest(Request $request)
            {
                return parent::getClientCredentials(Psr7Helper::yiiToPsr7Request($request));
            }
        };

        return $grant->getClientCredentialsFromYiiRequest($request);
    }
}
