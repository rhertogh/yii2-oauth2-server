<?php

namespace Yii2Oauth2ServerTests\unit\helpers;

use rhertogh\Yii2Oauth2Server\helpers\UrlHelper;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\helpers\UrlHelper
 */
class UrlHelperTest extends TestCase
{
    /**
     * @param string $url
     * @param array|null $params
     *
     * @dataProvider addQueryParamsProvider
     */
    public function testAddQueryParams($url, $params, $expected)
    {
        $this->assertEquals($expected, UrlHelper::addQueryParams($url, $params));
    }

    /**
     * @return array[]
     * @see testAddQueryParams()
     */
    public function addQueryParamsProvider()
    {
        // phpcs:disable Generic.Files.LineLength.TooLong -- readability actually better on single line
        return [
            ['/index', ['param1' => 'test1'], '/index?param1=test1'],
            ['/index.php', ['param1' => 'test1'], '/index.php?param1=test1'],
            ['localhost', ['param1' => 'test1'], 'localhost?param1=test1'],
            ['localhost/index', ['param1' => 'test1'], 'localhost/index?param1=test1'],
            ['https://localhost', ['param1' => 'test1'], 'https://localhost?param1=test1'],
            ['https://localhost?present1=1', ['param1' => 'test1'], 'https://localhost?present1=1&param1=test1'],
            ['localhost?present1=1', ['present1' => 'test1'], 'localhost?present1=test1'],
            ['localhost?present1=1', ['present1' => 'test1', 'param2' => 'test2'], 'localhost?present1=test1&param2=test2'],
            ['https://user:password@localhost:8080?present1=1&present2=2#fragment1=1', ['present2' => 'test2', 'param2' => 'test2'], 'https://user:password@localhost:8080?present1=1&present2=test2&param2=test2#fragment1=1'],
        ];
        // phpcs:enable Generic.Files.LineLength.TooLong
    }
}
