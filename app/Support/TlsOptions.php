<?php

namespace App\Support;

class TlsOptions
{
    /**
     * Use Windows' trusted root store when PHP has no CA bundle configured.
     * Certificate verification remains enabled.
     */
    public static function curl(): array
    {
        if (PHP_OS_FAMILY !== 'Windows' || ! defined('CURLOPT_SSL_OPTIONS') || ! defined('CURLSSLOPT_NATIVE_CA')) {
            return [];
        }

        return [CURLOPT_SSL_OPTIONS => CURLSSLOPT_NATIVE_CA];
    }
}
