<?php

use Lethanghsph\LaravelRemoteConfig\Services\RemoteConfig;

if (!function_exists('get_remote_config')) {
    /**
     * Get spring remote config
     *
     * @param string $configKey
     * @param string $configPath
     * @param null   $default
     * @return mixed
     */
    function get_remote_config(string $configKey, string $configPath, $default = null)
    {
        $configs = app(RemoteConfig::class)->get($configPath);

        if (isset($configs['propertySources']) && is_array($configs['propertySources'])) {
            $configKey = 'acme.' . $configKey;
            foreach ($configs['propertySources'] as $config) {
                if (isset($config['source'][$configKey])) {
                    return $config['source'][$configKey];
                }
            }
        }
        return $default;
    }
}
