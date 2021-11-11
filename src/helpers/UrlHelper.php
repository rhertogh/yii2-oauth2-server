<?php

namespace rhertogh\Yii2Oauth2Server\helpers;

use yii\helpers\ArrayHelper;

class UrlHelper
{
    /**
     * Add additional parameters to a URL
     * @param string $url
     * @param array $params
     * @return string
     * @since 1.0.0
     */
    public static function addQueryParams($url, $params)
    {
        $parts = parse_url($url);

        if (!empty($parts['query'])) {
            parse_str($parts['query'], $queryParams);
            $queryParams = ArrayHelper::merge($queryParams, $params);
        } else {
            $queryParams = $params;
        }

        $queryString = http_build_query($queryParams);

        return
            (isset($parts['scheme']) ? "{$parts['scheme']}:" : '') .
            ((isset($parts['user']) || isset($parts['host'])) ? '//' : '') .
            (isset($parts['user']) ? "{$parts['user']}" : '') .
            (isset($parts['pass']) ? ":{$parts['pass']}" : '') .
            (isset($parts['user']) ? '@' : '') .
            (isset($parts['host']) ? "{$parts['host']}" : '') .
            (isset($parts['port']) ? ":{$parts['port']}" : '') .
            (isset($parts['path']) ? "{$parts['path']}" : '') .
            (isset($queryString) ? "?$queryString" : '') .
            (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
    }
}
