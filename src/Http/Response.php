<?php

namespace Adext\Curl\Http;

use Psr\Http\Message\ResponseInterface;
use Adext\Curl\Http\Contracts\Response as ResponseContract;


class Response implements ResponseContract
{
    /**
     * The HTTP Response implementation.
     *
     * @var ResponseInterface
     */
    protected ResponseInterface $response;

    /**
     * Create a new Response instance.
     *
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Check if the call is successful by the response code.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->response->getStatusCode() >= 200 && $this->response->getStatusCode() < 300;
    }

    /**
     * Get the response body.
     *
     * @param bool $toArray
     * @return array|object|null
     */
    public function getBody(bool $toArray = false): array|object|null
    {
        return json_decode($this->response->getBody(), $toArray);
    }

    public function getRawBody()
    {
        return $this->response->getBody();
    }

    /**
     * Handle dynamic method calls into the Response.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters): mixed
    {
        return call_user_func_array([$this->response, $method], $parameters);
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
