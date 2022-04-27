<?php

namespace Adext\Curl\Http;

use Adext\Curl\Http\Contracts\Endpoint as EndpointContract;
use Adext\Curl\Http\Contracts\MicroService;
use Exception;

/**
 * Class Endpoint
 * @package App\Http\Curl\Endpoints
 */
abstract class Endpoint implements EndpointContract
{
    /**
     * The endpoint URI.
     *
     * @var string
     */
    protected string $uri;

    /**
     * The endpoint method.
     *
     * @var string
     */
    protected string $method;

    /**
     * The endpoint's options.
     *
     * @var array
     */
    protected array $options;

    /**
     * @var MicroService
     */
    protected MicroService $service;

    /**
     * @var array
     */
    protected array $required = [];

    /**
     * @param MicroService $service
     * @param array $options
     * @throws Exception
     */
    public function __construct(MicroService $service, array $options = [])
    {
        $this->service = $service;
        $this->options = $options;
        $this->validateRequest($this->options);
    }

    /**
     * @param $options
     * @throws Exception
     */
    protected function validateRequest($options): void
    {
        if (!empty($this->required)) {
            if (!isset($options['uri'])) {
                throw new Exception('Uri parameters are required');
            }

            $params = $options['uri'];

            foreach ($this->required as $key) {
                if (!array_key_exists($key, $params)) {
                    $mgs = sprintf("Field %s required", $key);
                    throw new Exception($mgs);
                }
            }
        }
    }

    /**
     * Get the endpoint method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method ?: 'GET';
    }

    /**
     * Get the endpoint options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get the endpoint URI.
     *
     * @return string
     */
    public function getUri(): string
    {
        $this->sanitizeGetRequest();
        return $this->normalize($this->service->uri()) . '/' . $this->normalize($this->uri ?: '');
    }

    /**
     *
     */
    protected function sanitizeGetRequest(): void
    {
        $opts = $this->options;
        $pattern = $this->uri;

        if (isset($opts['uri'])) {
            preg_match_all("/{(?<key>[[:alnum:]]+)}/", $pattern, $matches);

            foreach ($matches['key'] as $match) {
                if (isset($opts['uri'][$match])) {
                    $this->uri = str_replace("{" . $match . "}", $opts['uri'][$match], $this->uri);
                }
            }
        }

        unset($this->options['uri']);
    }

    /**
     * Normalize the given string.
     *
     * @param  string $string
     * @return string
     */
    protected function normalize(string $string): string
    {
        return trim($string, '/');
    }

    /**
     * Get the Service implementation in this endpoint.
     *
     * @return MicroService
     */
    public function getService(): MicroService
    {
        return $this->service;
    }
}
