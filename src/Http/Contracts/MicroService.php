<?php

namespace Adext\Curl\Http\Contracts;

interface MicroService
{
    /**
     * Get the microservice's base URI.
     *
     * @return string
     */
    public function uri(): string;

    /**
     * Get the microservice's name.
     *
     * @return string
     */
    public function name(): string;
}
