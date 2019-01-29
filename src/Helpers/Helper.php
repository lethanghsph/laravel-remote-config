<?php

use Lethanghsph\LaravelRemoteConfig\Services\RemoteConfig;

if (!function_exists('get_remote_config')) {
    /**
     * Get spring remote config
     *
     * @param string $configKey
     * @param string $configPath
     * @return mixed
     */
    function get_remote_config(string $configKey, string $configPath)
    {
        $configs = app(RemoteConfig::class)->get($configPath);

        try {
            $propertySources = $configs['propertySources'][0]['source'];
            $configKey       = 'spring.data.' . $configKey;
            return $propertySources[$configKey];
        } catch (\Exception $e) {
            return null;
        }
    }
};
