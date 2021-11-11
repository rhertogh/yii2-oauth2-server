<?php

namespace Yii2Oauth2ServerTests\_helpers;

/**
 *
 * ob_start(function ($buffer) {
 *    fwrite(STDOUT, $buffer);
 * });
 *
 * stream_filter_register('OutputBuffer', OutputBufferStreamFilter::class);
 * $stdOutBuffer = stream_filter_append(STDOUT, 'OutputBuffer');
 * OutputBufferStreamFilter::clearBuffer();
 * // run code
 * $stdOutBuffer && stream_filter_remove($stdOutBuffer);
 * echo OutputBufferStreamFilter::getBuffer();
 */
class OutputBufferStreamFilter extends \php_user_filter
{
    /**
     * Holds the output buffer
     * @var string
     */
    protected static $_buffer = '';

    /**
     * @inheritDoc
     */
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            self::$_buffer .= $bucket->data;
            $consumed += $bucket->datalen;
            $bucket->data = '';
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }

    /**
     * @return string The buffered output
     */
    public static function getBuffer()
    {
        return static::$_buffer;
    }

    /**
     * Resets the output buffer
     */
    public static function clearBuffer()
    {
        static::$_buffer = '';
    }
}
