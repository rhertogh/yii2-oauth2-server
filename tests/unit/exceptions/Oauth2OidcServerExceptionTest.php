<?php

namespace Yii2Oauth2ServerTests\unit\exceptions;

use Codeception\Util\HttpCode;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2OidcServerException;
use rhertogh\Yii2Oauth2Server\exceptions\Oauth2ServerHttpException;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\exceptions\Oauth2OidcServerException
 */
class Oauth2OidcServerExceptionTest extends TestCase
{
    public function testLoginRequiredException()
    {
        $redirectUri = 'https://localhost/redirect_uri';

        $exception = Oauth2OidcServerException::loginRequired($redirectUri);

        $this->assertInstanceOf(Oauth2OidcServerException::class, $exception);
        $this->assertEquals('login_required', $exception->getErrorType());
        $this->assertEquals(HttpCode::BAD_REQUEST, $exception->getHttpStatusCode());
    }

    public function testInteractionRequiredException()
    {
        $redirectUri = 'https://localhost/redirect_uri';

        $exception = Oauth2OidcServerException::interactionRequired($redirectUri);

        $this->assertInstanceOf(Oauth2OidcServerException::class, $exception);
        $this->assertEquals('interaction_required', $exception->getErrorType());
        $this->assertEquals(HttpCode::BAD_REQUEST, $exception->getHttpStatusCode());
    }

    public function testConsentRequiredException()
    {
        $redirectUri = 'https://localhost/redirect_uri';

        $exception = Oauth2OidcServerException::consentRequired($redirectUri);

        $this->assertInstanceOf(Oauth2OidcServerException::class, $exception);
        $this->assertEquals('consent_required', $exception->getErrorType());
        $this->assertEquals(HttpCode::BAD_REQUEST, $exception->getHttpStatusCode());
    }

    public function testAccountSelectionRequiredException()
    {
        $redirectUri = 'https://localhost/redirect_uri';

        $exception = Oauth2OidcServerException::accountSelectionRequired(null, $redirectUri);

        $this->assertInstanceOf(Oauth2OidcServerException::class, $exception);
        $this->assertEquals('account_selection_required', $exception->getErrorType());
        $this->assertEquals(HttpCode::BAD_REQUEST, $exception->getHttpStatusCode());
    }

    public function testRequestParameterNotSupported()
    {
        $redirectUri = 'https://localhost/redirect_uri';

        $exception = Oauth2OidcServerException::requestParameterNotSupported($redirectUri);

        $this->assertInstanceOf(Oauth2OidcServerException::class, $exception);
        $this->assertEquals('request_not_supported', $exception->getErrorType());
        $this->assertEquals(HttpCode::BAD_REQUEST, $exception->getHttpStatusCode());
    }

    public function testRequestUriParameterNotSupported()
    {
        $redirectUri = 'https://localhost/redirect_uri';

        $exception = Oauth2OidcServerException::requestUriParameterNotSupported($redirectUri);

        $this->assertInstanceOf(Oauth2OidcServerException::class, $exception);
        $this->assertEquals('request_uri_not_supported', $exception->getErrorType());
        $this->assertEquals(HttpCode::BAD_REQUEST, $exception->getHttpStatusCode());
    }
}
