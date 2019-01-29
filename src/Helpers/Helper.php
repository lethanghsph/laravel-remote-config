<?php

use Lethanghsph\LaravelRemoteConfig\Services\RemoteConfig;

if (!function_exists('get_remote_config')) {
    function get_remote_config(string $key, string $filePath)
    {
        $configs = app(RemoteConfig::class)->get($filePath);

        $keyParsed = explode('.', $key);
        $value = $configs;
        foreach ($keyParsed as $keyItem) {
            if (isset($value[$keyItem])) {
                $value = $value[$keyItem];
            } else {
                $value = null;
                break;
            }
        }
        return $value;
    }
};
