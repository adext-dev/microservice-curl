<?php

namespace Adext\Curl\Http\Contracts;

use GuzzleHttp\ClientInterface;

interface HttpClient
{
    /**
     * Get the HTTP Client implementation.
     *
     * @return ClientInterface
     */
    public function getClient(): ClientInterface;

    /**
     * Call an API by the given Endpoint object asynchronously.
     *
     * @param Endpoint $endpoint
     * @return mixed
     */
    public function callAsync(Endpoint $endpoint): mixed;

    /**
     * Call an API by the given Endpoint object.
     *
     * @param Endpoint $endpoint
     * @param bool $wait
     * @return mixed
     */
    public function call(Endpoint $endpoint, $wait = true): mixed;
}
