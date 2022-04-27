<?php

namespace Adext\Curl\Http;

use Adext\Curl\Http\Contracts\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class Service
{
    /**
     * @param Response $response
     * @return array|object|null
     */
    static public function getResponse(Response $response): array|object|null
    {
        if ($response->isSuccessful()) {
            return $response->getBody();
        }

        if (!is_null($response->getBody())) {
            $content = $response->getBody();
            $statusCode = (property_exists($content, 'status_code')) ? $content->status_code : 500;
            $message = (property_exists($content, 'message')) ? $content->message : '';
            $code = (property_exists($content, 'code')) ? $content->code : 2001;
            throw new HttpException($statusCode, $message, null, [], $code);
        }
        throw new HttpException(500, 'Unexpected error please try again later', null, [], 2001);
    }
}
