<?php

namespace Lethanghsph\LaravelRemoteConfig\Services;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise;

class RequestClient
{
    /**
     * @var GuzzleClient
     */
    private $guzzleClient = null;

    /**
     * @var array
     */
    private $config;

    public function __construct(array $config = null)
    {
        $this->config = [
            "host"     => env("EXTERNAL_CONFIG_SERVICE_HOST"),
            "user"     => env("EXTERNAL_CONFIG_SERVICE_USER"),
            "password" => env("EXTERNAL_CONFIG_SERVICE_PASSWORD"),
            "timeout"  => env("EXTERNAL_CONFIG_SERVICE_TIMEOUT", 5),
            "env"      => env("EXTERNAL_CONFIG_SERVICE_ENV"),
            "prefix"   => env("EXTERNAL_CONFIG_SERVICE_URI_PREFIX"),
        ];

        if (!is_null($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    public function getConfig(array $services)
    {
        $client = $this->getClient();
        $requestPromises = [];
        foreach ($services as $service) {
            $uri = "/{$service}";
            if ($this->config['env']) {
                $uri = "/{$this->config['env']}{$uri}";
            }
            if ($this->config['prefix']) {
                $uri = "{$this->config['prefix']}{$uri}";
            }
            $requestPromises[$service] = $client->getAsync($uri);
        }
        $results = Promise\settle($requestPromises)->wait();
        $arrayResult = [];
        foreach ($results as $key => $result) {
            if (isset($result['value']) && $result['state'] == 'fulfilled') {
                $arrayResult[$key] = $this->filterConfig(json_decode($result['value']->getBody()->getContents(), true));
            }
        }
        return $arrayResult;
    }

    private function getClient()
    {
        if (is_null($this->guzzleClient)) {
            $parameters = [
                'base_uri' => $this->config['host'],
                'timeout'  => $this->config['timeout']
            ];
            if ($this->config["user"] && $this->config["password"]) {
                $parameters['headers'] = ['Authorization' => 'Basic ' . base64_encode($this->config["user"] . ':' . $this->config["password"])];
            }
            $this->guzzleClient = new GuzzleClient($parameters);
        }
        return $this->guzzleClient;
    }

    /**
     * @param array $configResult
     * @return array
     */
    private function filterConfig(array $configResult)
    {
        foreach ($configResult as $keyConfig => $value) {
            if ($value == 'true') {
                $configResult[$keyConfig] = true;
            } elseif ($value == 'false') {
                $configResult[$keyConfig] = false;
            }
        }
        return $configResult;
    }
}