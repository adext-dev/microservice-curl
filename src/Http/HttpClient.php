<?php

namespace Adext\Curl\Http;

use GuzzleHttp\ClientInterface;
use Adext\Curl\Http\Contracts\Endpoint;
use Adext\Curl\Exceptions\CircuitBreakerException;
use Adext\Curl\Http\Contracts\HttpClient as HttpClientContract;
use Aws\Credentials\CredentialsInterface;
use Aws\Signature\SignatureV4;
use GuzzleHttp\ClientInterface as GuzzleHttpClientContract;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;

/**
 * Class HttpClient
 * @package App\Http\Http
 */
class HttpClient implements HttpClientContract
{
    /**
     * The Guzzle HTTP Client implementation.
     *
     * @var ClientInterface
     */
    protected ClientInterface $httpClient;

    /**
     * The circuit breaker implementation.
     *
     */
    protected $circuitBreaker;

    /**
     * @var CredentialsInterface
     */
    protected $credentials;

    /** @var SignatureV4 */
    protected $signature;

    /**
     * Create a new HttpClient instance.
     *
     * @param ClientInterface $httpClient
     */
    public function __construct(
        GuzzleHttpClientContract $httpClient
    )
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Call an API by the given Endpoint object.
     *
     * @param Endpoint $endpoint
     * @param bool $wait
     * @return mixed
     *
     * @throws CircuitBreakerException
     */
    public function call(Endpoint $endpoint, $wait = true): mixed
    {
        $this->checkEndpoint($endpoint);

        $method = $wait ? 'send' : 'sendAsync';

        $request = $this->getRequest($endpoint->getMethod(), $endpoint->getUri());

        $result = $this->getClient()->{$method}($request, $this->options($endpoint->getOptions()));

        return $wait ? new Response($result) : $result;
    }

    /**
     * Call an API by the given Endpoint object asynchronously.
     *
     * @param Endpoint $endpoint
     * @return mixed
     *
     * @throws CircuitBreakerException
     */
    public function callAsync(Endpoint $endpoint): mixed
    {
        return $this->call($endpoint, false);
    }

    /**
     * Get the HTTP Client implementation.
     *
     * @return ClientInterface
     */
    public function getClient(): ClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Handle dynamic method calls into the HttpClient.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters): mixed
    {
        return call_user_func_array([$this->getClient(), $method], $parameters);
    }

    /**
     * Check if the given endpoint is unavailable.
     *
     * @param Endpoint $endpoint
     * @return void
     *
     * @throws CircuitBreakerException
     */
    protected function checkEndpoint(Endpoint $endpoint): void
    {
        $key = sha1($endpoint->getUri());
    }

    /**
     * Returns the options when call an API.
     *
     * @param array $options
     * @return array
     */
    protected function options(array $options = []): array
    {
        $defaults = [
            RequestOptions::CONNECT_TIMEOUT => config('services.connection.connect_timeout'),
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::TIMEOUT => config('services.connection.timeout')
        ];

        return array_merge($defaults, $options);
    }

    /**
     * @param string $method
     * @param string $uri
     * @return Request
     */
    protected function getRequest(string $method, string $uri): Request
    {
        return new Request($method, $uri);
    }
}
