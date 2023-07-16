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

        $parts['query'] = http_build_query($queryParams);

        return static::buildUrl($parts);
    }

    public static function buildUrl(array $components)
    {
        return
            (isset($components['scheme']) ? "{$components['scheme']}:" : '') .
            ((isset($components['user']) || isset($components['host'])) ? '//' : '') .
            (isset($components['user']) ? "{$components['user']}" : '') .
            (isset($components['pass']) ? ":{$components['pass']}" : '') .
            (isset($components['user']) ? '@' : '') .
            (isset($components['host']) ? "{$components['host']}" : '') .
            (isset($components['port']) ? ":{$components['port']}" : '') .
            (isset($components['path']) ? "{$components['path']}" : '') .
            (isset($components['query']) ? "?{$components['query']}" : '') .
            (isset($components['fragment']) ? "#{$components['fragment']}" : '');
    }

    public static function stripQueryAndFragment($url)
    {
        $components = parse_url($url);
        unset($components['query']);
        unset($components['fragment']);
        return UrlHelper::buildUrl($components);
    }
}
