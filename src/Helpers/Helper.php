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
            foreach ($configs['propertySources'] as $config) {
                $configKey = 'acme.' . $configKey;
                if (isset($config['source'][$configKey])) {
                    return $config['source'][$configKey];
                }
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
};
