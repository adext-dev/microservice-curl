<?php

namespace Adext\Curl\Http\Contracts;

interface Response
{
    /**
     * Check if the call is successful by the response code.
     *
     * @return bool
     */
    public function isSuccessful(): bool;

    /**
     * Get the response body.
     *
     * @param bool $toArray
     * @return array|object|null
     */
    public function getBody(bool $toArray = false): array|object|null;
}
