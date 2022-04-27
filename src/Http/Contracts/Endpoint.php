<?php

namespace Adext\Curl\Http\Contracts;

interface Endpoint
{
    /**
     * Get the endpoint URI.
     *
     * @return string
     */
    public function getUri(): string;

    /**
     * Get the endpoint method.
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * Get the endpoint options.
     *
     * @return array
     */
    public function getOptions(): array;
}
