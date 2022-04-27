<?php

namespace Adext\Curl\Http;

use RuntimeException;
use Adext\Curl\Http\Contracts\Endpoint;
use Adext\Curl\Http\Contracts\HttpClient as HttpClientContract;
use Adext\Curl\Http\Contracts\MicroService;

abstract class Facade
{
    /**
     * The service class name.
     *
     * @var ?string
     */
    protected ?string $service = null;

    /**
     * The Http Client implementation.
     *,
     * @var HttpClientContract
     */
    protected static HttpClientContract $httpClient;

    /**
     * Set the HttpClient for the Facade.
     *
     * @param HttpClientContract $httpClient
     * @return void
     */
    public static function setHttpClient(HttpClientContract $httpClient): void
    {
        static::$httpClient = $httpClient;
    }

    /**
     * Get the service instance.
     *
     * @return MicroService
     */
    protected function getService(): MicroService
    {
        $service = 'Src\Infrastructure\Http\Microservices\\' . $this->getServiceClassName() . 'Microservice';

        return new $service;
    }

    /**
     * Get the service class name.
     *
     * @return string
     */
    protected function getServiceClassName(): string
    {
        return $this->service = last(explode('\\', static::class));
    }

    /**
     * Get the endpoint instance.
     *
     * @param string $class
     * @param array $parameters
     * @return Endpoint
     */
    protected function getEndpoint(string $class, array $parameters = []): Endpoint
    {
        $endpoint = $this->getEndpointClassName($class);

        return new $endpoint($this->getService(), ...$parameters);
    }

    /**
     * Get the full endpoint class name by given class name.
     *
     * @param string $class
     * @return string
     */
    protected function getEndpointClassName(string $class): string
    {
        return 'Src\Infrastructure\Http\Endpoints\\' . $this->getServiceClassName() . '\\' . studly_case($class) . 'Endpoint';
    }

    /**
     * Handle dynamic method calls into the Facade.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws \RuntimeException
     */
    public function __call($method, array $parameters): mixed
    {
        if (!static::$httpClient instanceof HttpClientContract) {
            throw new RuntimeException('httpClient is not an instance of ' . HttpClientContract::class . '.');
        }

        $endpoint = $this->getEndpoint($method, $parameters);

        return static::$httpClient->call($endpoint);
    }

    /**
     * Handle dynamic static method calls into the Facade.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        return call_user_func_array([new static, $method], $parameters);
    }
}
