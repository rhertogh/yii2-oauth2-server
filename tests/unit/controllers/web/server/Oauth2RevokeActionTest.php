<?php

namespace Yii2Oauth2ServerTests\unit\controllers\web\server;

use Codeception\Util\HttpCode;
use rhertogh\Yii2Oauth2Server\components\repositories\Oauth2AccessTokenRepository;
use rhertogh\Yii2Oauth2Server\components\repositories\Oauth2RefreshTokenRepository;
use rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2RevokeAction;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2ServerHttpException;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use Yii2Oauth2ServerTests\unit\DatabaseTestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\controllers\web\server\Oauth2RevokeAction
 */
class Oauth2RevokeActionTest extends DatabaseTestCase
{
    /**
     * @dataProvider revokeProvider()
     */
    public function testRevoke(
        $token,
        $tokenTypeHint,
        $clientCredentials,
        $refreshTokenIdentifier,
        $accessTokenIdentifier,
        $statusCode,
        $errorDescription,
        $expectedLogMessage
    ) {
        $this->mockWebApplication();
        $module = Oauth2Module::getInstance();

        $controller = new Controller('test', $module);
        $revokeAction = new Oauth2RevokeAction('test', $controller);

        if ($refreshTokenIdentifier) {
            $mockRefreshTokenRepository = $this->getMockBuilder(Oauth2RefreshTokenRepository::class)->getMock();
            $mockRefreshTokenRepository
                ->expects($this->once())
                ->method('revokeRefreshToken')
                ->with($this->equalTo($refreshTokenIdentifier));

            $module->setRefreshTokenRepository($mockRefreshTokenRepository);
        }

        if ($accessTokenIdentifier) {
            $mockAccessTokenRepository = $this->getMockBuilder(Oauth2AccessTokenRepository::class)->getMock();
            $mockAccessTokenRepository
                ->expects($this->once())
                ->method('revokeAccessToken')
                ->with($this->equalTo($accessTokenIdentifier));

            $module->setAccessTokenRepository($mockAccessTokenRepository);
        }

        Yii::$app->request->setBodyParams([
            'token' => $token,
            'token_type_hint' => $tokenTypeHint,
        ]);

        if ($clientCredentials) {
            Yii::$app->request->headers->set(
                'Authorization',
                'Basic ' . base64_encode(implode(':', $clientCredentials))
            );
        }

        if ($expectedLogMessage) {
            $logTestPassed = false;
            $mockLogger = $this->getMockBuilder(yii\log\Logger::class)->getMock();
            $mockLogger
                ->expects($this->atLeastOnce())
                ->method('log')
                ->with(
                    $this->callback(function ($message) use (&$logTestPassed, $expectedLogMessage) {
                        if (str_contains($message, $expectedLogMessage)) {
                            $logTestPassed = true;
                        }
                        return true;
                    }),
                );
            $origLogger = Yii::getLogger();
            Yii::setLogger($mockLogger);
        }

        $response = $revokeAction->run();

        if (isset($origLogger)) {
            Yii::setLogger($origLogger);
            $this->assertTrue($logTestPassed, 'Expected logger to be called with message "' . $expectedLogMessage . '".'); // phpcs:ignore Generic.Files.LineLength.TooLong
        }

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($statusCode, $response->statusCode);
        if ($errorDescription) {
            $this->assertStringContainsString($errorDescription, $response->data['error_description']);
        }
    }

    /**
     * @return array[]
     * @see testRevoke()
     */
    public function revokeProvider()
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'refresh token' => [
                'token' => 'def50200abbe4150b3ba4bfee9642ef6c804e922eb591d214529517d8aa3bb93e3ad20ec71aa88077276bf6d96cb0314f0c50171bfa688d4e0b0e63b7d6217e31e2fa486dfd8e4ce570b1ffeb239007865511b29f8419cf4ad62fa21d5a04ff4f0c193faec28916aafb9e447c1bd800048db0f13f768e4f314aeebe3fd49ae75f6d528e5c8d90324f488ff2d62d39e2a8d426e3793e061cc1dbd83a58f45f5b6a7459aba3d3e4173773008eefa09633456ac1b7359fcad43d35ed0774e8f69c9103e1e33e89bdfb84b640663d97d3354367dd40590b4bd7819ef5bc57ce46ab2a1af4072025fcb64ec9684a5a7f8141a8a0356e0f67dd0da0e9eb4aae3afb917d5176883658edda68aa5a77cd111cd19a6c40e0f6e15412a5d23ce20d5c97606488d5103cb1d7b9e581ae7cf0402c15c4de2196e75e588551826f2a9ef1fe737841b1dfb6eb7f7018f21153550dda17c059481c8691b79db005bba1135c1f54c59c3b4219b36eaa36fdf34db9e1ed89ae3ac8b126cffeb53ac7ea0e0a39daaa43a5a8ab8562d717896a01bbf03971474a4b1c6bf4b14f23c4415843c93f5f12cde36b8753817c17da0363dba4cfd39b7621df96d55876c317e00dfc67b21ffdb47dc7a10dab12623f99f679e25938a98a67ec6aaa2a2881f8027ce863a546f0663fb671da30868a6c6b77e0dea805d41f8d12b442e339fae8bb482960620db24bf7d6ef2309e77b01fc960e25026c4c3c8450c0880380801d2a5ef6f6778a22a0fe9756bd7b7b52a0c4a6cf5226ac81f6ee08adcfc9f741d252afafe64c124d33fd0e4d43b0b94414b0d4614a5',
                'tokenTypeHint' => 'refresh_token',
                'clientCredentials' => ['test-client-type-auth-code-valid', 'secret'],
                'refreshTokenIdentifier' => '83c1f3255e8df30610c22fffcd10063ef326eccd5b4f114b29e645ed74276220989e2f00da12828b',
                'accessTokenIdentifier' => '8e8c616667aadf8a7682280795778135168586b05221193e76ad3fe61dd45f5ce7f76a813f5a12fa',
                'statusCode' => HttpCode::OK,
                'errorDescription' => null,
                'expectedLogMessage' => null,
            ],
            'access token' => [
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJ0ZXN0LWNsaWVudC10eXBlLWF1dGgtY29kZS12YWxpZCIsImp0aSI6IjhlOGM2MTY2NjdhYWRmOGE3NjgyMjgwNzk1Nzc4MTM1MTY4NTg2YjA1MjIxMTkzZTc2YWQzZmU2MWRkNDVmNWNlN2Y3NmE4MTNmNWExMmZhIiwiaWF0IjoxNzE0ODU2NTYyLjc1MzgxMSwibmJmIjoxNzE0ODU2NTYyLjc1MzgxNSwiZXhwIjoxNzE0ODYzNzYyLCJzdWIiOiIxMjMiLCJzY29wZXMiOlsidXNlci5pZC5yZWFkIiwiYXBwbGllZC1ieS1kZWZhdWx0IiwiYXBwbGllZC1ieS1kZWZhdWx0LWZvci1jbGllbnQiLCJhcHBsaWVkLWJ5LWRlZmF1bHQtYnktY2xpZW50LW5vdC1yZXF1aXJlZC1mb3ItY2xpZW50IiwiYXBwbGllZC1hdXRvbWF0aWNhbGx5LWJ5LWRlZmF1bHQiLCJhcHBsaWVkLWF1dG9tYXRpY2FsbHktYnktZGVmYXVsdC1mb3ItY2xpZW50Il0sImNsaWVudF9pZCI6InRlc3QtY2xpZW50LXR5cGUtYXV0aC1jb2RlLXZhbGlkIn0.qZ91vBXegYPxWNNPx9MNssWgKZAq_AO06TA3d7mNmg7AuwDgaSSeBeok5aOr4ASqFRAQ1LnF8xNxgd2Ar9SrRLqV_mozlSlnKxMLUItRPNu4xgU1P-bzMRXRsiuqsz5W4DTOrFRS4E_mJ7ryb8vqnogRSw449AM_WrP0SBdep3A5-Ft1Abca6kVoxOXfPXVxIMbbZjEiz47sEvfphh3qEVlEvp7aVHL1uD5zxs7qyDIdiM68R65u6n4W3y3WJTy-CLtoz_0ZO5avSx5cGmGvqImAO69NSRrmNnppWxG7t5B2dyFX3SEPXJHzJaWFxbhIHZ7P3O5EaxHFLAjEwdGhdw',
                'tokenTypeHint' => 'access_token',
                'clientCredentials' => ['test-client-type-auth-code-valid', 'secret'],
                'refreshTokenIdentifier' => null,
                'accessTokenIdentifier' => '8e8c616667aadf8a7682280795778135168586b05221193e76ad3fe61dd45f5ce7f76a813f5a12fa',
                'statusCode' => HttpCode::OK,
                'errorDescription' => null,
                'expectedLogMessage' => null,
            ],

            'no token' => [
                'token' => null,
                'tokenTypeHint' => null,
                'clientCredentials' => null,
                'refreshTokenIdentifier' => null,
                'accessTokenIdentifier' => null,
                'statusCode' => HttpCode::BAD_REQUEST,
                'errorDescription' => 'The `token` body parameter is required.',
                'expectedLogMessage' => null,
            ],

            'missing client credentials' => [
                'token' => 'def50200abbe4150b3ba4bfee9642ef6c804e922eb591d214529517d8aa3bb93e3ad20ec71aa88077276bf6d96cb0314f0c50171bfa688d4e0b0e63b7d6217e31e2fa486dfd8e4ce570b1ffeb239007865511b29f8419cf4ad62fa21d5a04ff4f0c193faec28916aafb9e447c1bd800048db0f13f768e4f314aeebe3fd49ae75f6d528e5c8d90324f488ff2d62d39e2a8d426e3793e061cc1dbd83a58f45f5b6a7459aba3d3e4173773008eefa09633456ac1b7359fcad43d35ed0774e8f69c9103e1e33e89bdfb84b640663d97d3354367dd40590b4bd7819ef5bc57ce46ab2a1af4072025fcb64ec9684a5a7f8141a8a0356e0f67dd0da0e9eb4aae3afb917d5176883658edda68aa5a77cd111cd19a6c40e0f6e15412a5d23ce20d5c97606488d5103cb1d7b9e581ae7cf0402c15c4de2196e75e588551826f2a9ef1fe737841b1dfb6eb7f7018f21153550dda17c059481c8691b79db005bba1135c1f54c59c3b4219b36eaa36fdf34db9e1ed89ae3ac8b126cffeb53ac7ea0e0a39daaa43a5a8ab8562d717896a01bbf03971474a4b1c6bf4b14f23c4415843c93f5f12cde36b8753817c17da0363dba4cfd39b7621df96d55876c317e00dfc67b21ffdb47dc7a10dab12623f99f679e25938a98a67ec6aaa2a2881f8027ce863a546f0663fb671da30868a6c6b77e0dea805d41f8d12b442e339fae8bb482960620db24bf7d6ef2309e77b01fc960e25026c4c3c8450c0880380801d2a5ef6f6778a22a0fe9756bd7b7b52a0c4a6cf5226ac81f6ee08adcfc9f741d252afafe64c124d33fd0e4d43b0b94414b0d4614a5',
                'tokenTypeHint' => 'refresh_token',
                'clientCredentials' => null,
                'refreshTokenIdentifier' => null,
                'accessTokenIdentifier' => null,
                'statusCode' => HttpCode::FORBIDDEN,
                'errorDescription' => 'Client authentication is required for confidential clients.',
                'expectedLogMessage' => null,
            ],
            'invalid client credentials' => [
                'token' => 'def50200abbe4150b3ba4bfee9642ef6c804e922eb591d214529517d8aa3bb93e3ad20ec71aa88077276bf6d96cb0314f0c50171bfa688d4e0b0e63b7d6217e31e2fa486dfd8e4ce570b1ffeb239007865511b29f8419cf4ad62fa21d5a04ff4f0c193faec28916aafb9e447c1bd800048db0f13f768e4f314aeebe3fd49ae75f6d528e5c8d90324f488ff2d62d39e2a8d426e3793e061cc1dbd83a58f45f5b6a7459aba3d3e4173773008eefa09633456ac1b7359fcad43d35ed0774e8f69c9103e1e33e89bdfb84b640663d97d3354367dd40590b4bd7819ef5bc57ce46ab2a1af4072025fcb64ec9684a5a7f8141a8a0356e0f67dd0da0e9eb4aae3afb917d5176883658edda68aa5a77cd111cd19a6c40e0f6e15412a5d23ce20d5c97606488d5103cb1d7b9e581ae7cf0402c15c4de2196e75e588551826f2a9ef1fe737841b1dfb6eb7f7018f21153550dda17c059481c8691b79db005bba1135c1f54c59c3b4219b36eaa36fdf34db9e1ed89ae3ac8b126cffeb53ac7ea0e0a39daaa43a5a8ab8562d717896a01bbf03971474a4b1c6bf4b14f23c4415843c93f5f12cde36b8753817c17da0363dba4cfd39b7621df96d55876c317e00dfc67b21ffdb47dc7a10dab12623f99f679e25938a98a67ec6aaa2a2881f8027ce863a546f0663fb671da30868a6c6b77e0dea805d41f8d12b442e339fae8bb482960620db24bf7d6ef2309e77b01fc960e25026c4c3c8450c0880380801d2a5ef6f6778a22a0fe9756bd7b7b52a0c4a6cf5226ac81f6ee08adcfc9f741d252afafe64c124d33fd0e4d43b0b94414b0d4614a5',
                'tokenTypeHint' => 'refresh_token',
                'clientCredentials' => ['test-client-type-auth-code-valid', 'wrong_secret'],
                'refreshTokenIdentifier' => null,
                'accessTokenIdentifier' => null,
                'statusCode' => HttpCode::FORBIDDEN,
                'errorDescription' => 'Invalid client authentication.',
                'expectedLogMessage' => null,
            ],

            'invalid token type hint' => [
                'token' => 'def50200abbe4150b3ba4bfee9642ef6c804e922eb591d214529517d8aa3bb93e3ad20ec71aa88077276bf6d96cb0314f0c50171bfa688d4e0b0e63b7d6217e31e2fa486dfd8e4ce570b1ffeb239007865511b29f8419cf4ad62fa21d5a04ff4f0c193faec28916aafb9e447c1bd800048db0f13f768e4f314aeebe3fd49ae75f6d528e5c8d90324f488ff2d62d39e2a8d426e3793e061cc1dbd83a58f45f5b6a7459aba3d3e4173773008eefa09633456ac1b7359fcad43d35ed0774e8f69c9103e1e33e89bdfb84b640663d97d3354367dd40590b4bd7819ef5bc57ce46ab2a1af4072025fcb64ec9684a5a7f8141a8a0356e0f67dd0da0e9eb4aae3afb917d5176883658edda68aa5a77cd111cd19a6c40e0f6e15412a5d23ce20d5c97606488d5103cb1d7b9e581ae7cf0402c15c4de2196e75e588551826f2a9ef1fe737841b1dfb6eb7f7018f21153550dda17c059481c8691b79db005bba1135c1f54c59c3b4219b36eaa36fdf34db9e1ed89ae3ac8b126cffeb53ac7ea0e0a39daaa43a5a8ab8562d717896a01bbf03971474a4b1c6bf4b14f23c4415843c93f5f12cde36b8753817c17da0363dba4cfd39b7621df96d55876c317e00dfc67b21ffdb47dc7a10dab12623f99f679e25938a98a67ec6aaa2a2881f8027ce863a546f0663fb671da30868a6c6b77e0dea805d41f8d12b442e339fae8bb482960620db24bf7d6ef2309e77b01fc960e25026c4c3c8450c0880380801d2a5ef6f6778a22a0fe9756bd7b7b52a0c4a6cf5226ac81f6ee08adcfc9f741d252afafe64c124d33fd0e4d43b0b94414b0d4614a5',
                'tokenTypeHint' => 'does_not_exist',
                'clientCredentials' => ['test-client-type-auth-code-valid', 'secret'],
                'refreshTokenIdentifier' => '83c1f3255e8df30610c22fffcd10063ef326eccd5b4f114b29e645ed74276220989e2f00da12828b',
                'accessTokenIdentifier' => '8e8c616667aadf8a7682280795778135168586b05221193e76ad3fe61dd45f5ce7f76a813f5a12fa',
                'statusCode' => HttpCode::OK,
                'errorDescription' => null,
                'expectedLogMessage' => 'The client specified an unknown `token_type_hint` "does_not_exist".',
            ],

            'incorrect refresh_token token type hint' => [
                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJ0ZXN0LWNsaWVudC10eXBlLWF1dGgtY29kZS12YWxpZCIsImp0aSI6IjhlOGM2MTY2NjdhYWRmOGE3NjgyMjgwNzk1Nzc4MTM1MTY4NTg2YjA1MjIxMTkzZTc2YWQzZmU2MWRkNDVmNWNlN2Y3NmE4MTNmNWExMmZhIiwiaWF0IjoxNzE0ODU2NTYyLjc1MzgxMSwibmJmIjoxNzE0ODU2NTYyLjc1MzgxNSwiZXhwIjoxNzE0ODYzNzYyLCJzdWIiOiIxMjMiLCJzY29wZXMiOlsidXNlci5pZC5yZWFkIiwiYXBwbGllZC1ieS1kZWZhdWx0IiwiYXBwbGllZC1ieS1kZWZhdWx0LWZvci1jbGllbnQiLCJhcHBsaWVkLWJ5LWRlZmF1bHQtYnktY2xpZW50LW5vdC1yZXF1aXJlZC1mb3ItY2xpZW50IiwiYXBwbGllZC1hdXRvbWF0aWNhbGx5LWJ5LWRlZmF1bHQiLCJhcHBsaWVkLWF1dG9tYXRpY2FsbHktYnktZGVmYXVsdC1mb3ItY2xpZW50Il0sImNsaWVudF9pZCI6InRlc3QtY2xpZW50LXR5cGUtYXV0aC1jb2RlLXZhbGlkIn0.qZ91vBXegYPxWNNPx9MNssWgKZAq_AO06TA3d7mNmg7AuwDgaSSeBeok5aOr4ASqFRAQ1LnF8xNxgd2Ar9SrRLqV_mozlSlnKxMLUItRPNu4xgU1P-bzMRXRsiuqsz5W4DTOrFRS4E_mJ7ryb8vqnogRSw449AM_WrP0SBdep3A5-Ft1Abca6kVoxOXfPXVxIMbbZjEiz47sEvfphh3qEVlEvp7aVHL1uD5zxs7qyDIdiM68R65u6n4W3y3WJTy-CLtoz_0ZO5avSx5cGmGvqImAO69NSRrmNnppWxG7t5B2dyFX3SEPXJHzJaWFxbhIHZ7P3O5EaxHFLAjEwdGhdw',
                'tokenTypeHint' => 'refresh_token',
                'clientCredentials' => ['test-client-type-auth-code-valid', 'secret'],
                'refreshTokenIdentifier' => null,
                'accessTokenIdentifier' => '8e8c616667aadf8a7682280795778135168586b05221193e76ad3fe61dd45f5ce7f76a813f5a12fa',
                'statusCode' => HttpCode::OK,
                'errorDescription' => null,
                'expectedLogMessage' => 'The client specified the `token_type_hint` as "refresh_token", however the server is unable to parse the `token` as such',
            ],

            'incorrect access_token token type hint' => [
                'token' => 'def50200abbe4150b3ba4bfee9642ef6c804e922eb591d214529517d8aa3bb93e3ad20ec71aa88077276bf6d96cb0314f0c50171bfa688d4e0b0e63b7d6217e31e2fa486dfd8e4ce570b1ffeb239007865511b29f8419cf4ad62fa21d5a04ff4f0c193faec28916aafb9e447c1bd800048db0f13f768e4f314aeebe3fd49ae75f6d528e5c8d90324f488ff2d62d39e2a8d426e3793e061cc1dbd83a58f45f5b6a7459aba3d3e4173773008eefa09633456ac1b7359fcad43d35ed0774e8f69c9103e1e33e89bdfb84b640663d97d3354367dd40590b4bd7819ef5bc57ce46ab2a1af4072025fcb64ec9684a5a7f8141a8a0356e0f67dd0da0e9eb4aae3afb917d5176883658edda68aa5a77cd111cd19a6c40e0f6e15412a5d23ce20d5c97606488d5103cb1d7b9e581ae7cf0402c15c4de2196e75e588551826f2a9ef1fe737841b1dfb6eb7f7018f21153550dda17c059481c8691b79db005bba1135c1f54c59c3b4219b36eaa36fdf34db9e1ed89ae3ac8b126cffeb53ac7ea0e0a39daaa43a5a8ab8562d717896a01bbf03971474a4b1c6bf4b14f23c4415843c93f5f12cde36b8753817c17da0363dba4cfd39b7621df96d55876c317e00dfc67b21ffdb47dc7a10dab12623f99f679e25938a98a67ec6aaa2a2881f8027ce863a546f0663fb671da30868a6c6b77e0dea805d41f8d12b442e339fae8bb482960620db24bf7d6ef2309e77b01fc960e25026c4c3c8450c0880380801d2a5ef6f6778a22a0fe9756bd7b7b52a0c4a6cf5226ac81f6ee08adcfc9f741d252afafe64c124d33fd0e4d43b0b94414b0d4614a5',
                'tokenTypeHint' => 'access_token',
                'clientCredentials' => ['test-client-type-auth-code-valid', 'secret'],
                'refreshTokenIdentifier' => '83c1f3255e8df30610c22fffcd10063ef326eccd5b4f114b29e645ed74276220989e2f00da12828b',
                'accessTokenIdentifier' => '8e8c616667aadf8a7682280795778135168586b05221193e76ad3fe61dd45f5ce7f76a813f5a12fa',
                'statusCode' => HttpCode::OK,
                'errorDescription' => null,
                'expectedLogMessage' => 'The client specified the `token_type_hint` as "access_token", however the server is unable to parse the `token` as such',
            ],
        ];
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    public function testRevokeDisabled()
    {
        $this->mockWebApplication([
            'modules' => [
                'oauth2' => [
                    'enableTokenRevocation' => false,
                ],
            ],
        ]);
        $module = Oauth2Module::getInstance();

        $controller = new Controller('test', $module);
        $revokeAction = new Oauth2RevokeAction('test', $controller);

        $response = $revokeAction->run();
        $this->assertEquals(HttpCode::NOT_FOUND, $response->statusCode);
        $this->assertEquals('Not Found', $response->data['error']);
    }
}
