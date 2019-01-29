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
            "host"     => config("laravel-remote-config.host"),
            "user"     => config("laravel-remote-config.user"),
            "password" => config("laravel-remote-config.password"),
            "timeout"  => config("laravel-remote-config.timeout", 5),
            "env"      => config("laravel-remote-config.env"),
            "prefix"   => config("laravel-remote-config.prefix"),
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
            $uri = "/{$service}.json";
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