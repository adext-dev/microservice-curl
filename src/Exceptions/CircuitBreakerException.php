<?php

namespace Adext\Curl\Exceptions;

use RuntimeException;
use GuzzleHttp\Exception\GuzzleException;

class CircuitBreakerException extends RuntimeException implements GuzzleException
{
    //
}
