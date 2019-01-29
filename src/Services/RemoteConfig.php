<?php

namespace Lethanghsph\LaravelRemoteConfig\Services;

class RemoteConfig
{
    const CACHE_KEY = "config_cache_key";

    /**
     * @var RequestClient
     */
    private static $client = null;

    /**
     * @var array
     */
    private static $registryConfig = [];

    /**
     * @var bool
     */
    private static $cacheEnabled = false;

    /**
     * @param string|array $services
     * @return array
     */
    static function get($services)
    {
        $arrayConfigResult = [];
        $responseConfigArray = [];
        self::setCacheEnable();
        $isUpdate = false;
        // Recovery config from cache
        self::fetchConfig();
        if (is_null(self::$client)) {
            self::$client = new RequestClient();
        }
        if (is_array($services)) {
            foreach ($services as $key => $service) {
                $arrayConfigResult[$service] = null;
                if (isset(self::$registryConfig[$service])) {
                    unset($services[$key]);
                }
            }
            if (!empty($services)) {
                $isUpdate = true;
                $responseConfigArray = self::$client->getConfig($services);
            }
        } elseif (!isset(self::$registryConfig[$services])) {
            $isUpdate = true;
            $responseConfigArray = self::$client->getConfig([$services]);
        }
        self::$registryConfig = array_merge(self::$registryConfig, $responseConfigArray);
        // Store config on cache
        self::storeCache($isUpdate);
        if (is_array($services)) {
            foreach ($arrayConfigResult as $key => $item) {
                $arrayConfigResult[$key] = self::$registryConfig[$key];
            }
            return $arrayConfigResult;
        }
        return self::$registryConfig[$services];
    }

    /**
     * @param bool $isUpdate
     */
    private static function storeCache($isUpdate = false)
    {
        if (self::$cacheEnabled && $isUpdate && !is_null(self::$registryConfig)) {
            $ttl = getenv('CONFIG_CACHE_TTL');
            $ttl = $ttl ? $ttl : 3600;
            apcu_store(self::CACHE_KEY, self::$registryConfig, $ttl);
        }
    }

    private static function fetchConfig()
    {
        if (self::$cacheEnabled && apcu_exists(self::CACHE_KEY)) {
            $config = apcu_fetch(self::CACHE_KEY);
            if (!is_null($config)) {
                self::$registryConfig = $config;
            } else {
                self::$registryConfig = [];
            }
        }
    }

    public static function clearCacheConfig()
    {
        if (self::$cacheEnabled) {
            apcu_delete(self::CACHE_KEY);
        }
    }

    private static function setCacheEnable()
    {
        $isEnable = getenv('CONFIG_CACHE_ENABLE');
        if (is_string($isEnable)) {
            self::$cacheEnabled = 'true' == getenv('CONFIG_CACHE_ENABLE');
        } elseif (is_bool($isEnable)) {
            self::$cacheEnabled = $isEnable;
        }
    }
}