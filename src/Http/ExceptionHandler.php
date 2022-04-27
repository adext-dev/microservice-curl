<?php

namespace Adext\Curl\Http;

use Adext\Curl\Exceptions\CircuitBreakerException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use RuntimeException;

class ExceptionHandler
{
    /**
     * The CircuitBreaker implementation.
     *
     */
    protected static $circuitBreaker;
    /**
     * The GuzzleException implementation.
     *
     * @var GuzzleException
     */
    protected $e;
    /**
     * The exception handler mapping for the application.
     *
     * Example: 'ExceptionClassName' => 'MethodThatWillHandleTheExceptionInthisClass'
     *
     * @var array
     */
    protected $handler = [
        ConnectException::class => 'ConnectException',
        CircuitBreakerException::class => 'CircuitBreakerException',
        RequestException::class => 'RequestException',
    ];

    /**
     * Create a new exception handler instance.
     *
     * @param  GuzzleException $e
     */
    public function __construct(GuzzleException $e)
    {
        $this->e = $e;
    }

    /**
     * Handle the exception and return an HTTP response
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \RuntimeException
     */
    public function handle()
    {
        $e = get_class($this->e);

        if (!isset($this->handler[$e])) {
            throw new RuntimeException('Handler is unavailable for this exception [' . $e . '].');
        }

        $method = $this->handler[$e];

        return $this->{'render' . $method}($this->e);
    }


    /**
     * Render the ConnectException into an HTTP response.
     *
     * @param ConnectException $e
     * @return Response
     * @internal param ConnectException $exception
     */
    protected function renderConnectException(ConnectException $e)
    {
        return $this->toResponse($e);
    }

    /**
     * Render the CircuitBreakerException into an HTTP response.
     *
     * @param CircuitBreakerException $e
     * @return Response
     * @internal param CircuitBreakerException $exception
     */
    protected function renderCircuitBreakerException(CircuitBreakerException $e)
    {
        return $this->toResponse($e, ['message' => $e->getMessage()], 503);
    }

    /**
     * @param RequestException $e
     * @return Response
     */
    protected function renderRequestException(RequestException $e)
    {
        return $this->toResponse($e, ['message' => $e->getMessage()], 404);
    }


    /**
     * Render the given exception into an HTTP response.
     *
     * @param  GuzzleException $e
     * @return Response
     */
    protected function toResponse(GuzzleException $e, array $data = [], $httpCode = 0)
    {
        return response_json(
            $data ?: ['message' => $this->buildErrorMessage($e)],
            $httpCode ?: $this->buildHttpCode($e)
        );
    }

    /**
     * Build an error message by given exception.
     *
     * @param  GuzzleException $e
     * @return string
     */
    protected function buildErrorMessage(GuzzleException $e)
    {
        if ($e instanceof RequestException) {
            return trans('curl.' . CurlHttpCode::errorNumber($e));
        }

        return $e->getMessage();
    }

    /**
     * Build the HTTP code by given exception.
     *
     * @param  GuzzleException $e
     * @return int
     */
    protected function buildHttpCode(GuzzleException $e)
    {
        if ($e instanceof RequestException) {
            $this->track($e, $code = CurlHttpCode::generate($e));

            return $code;
        }

        return 500;
    }

    /**
     * Tracks the error request.
     *
     * @param  GuzzleException $e
     * @param  int $code
     * @return void
     */
    protected function track(GuzzleException $e, $code)
    {
        if (static::$circuitBreaker instanceof CircuitBreakerInterface
            && $e instanceof RequestException && 503 == $code) {
            $key = $this->resolveRequestSignature($e);

            static::$circuitBreaker->track($key, config('services.connection.circuit_breaker_decay'));
        }
    }

    /**
     * Resolve the signature for the error request.
     *
     * @param  RequestException $e
     * @return string
     */
    protected function resolveRequestSignature(RequestException $e)
    {
        return sha1($e->getRequest()->getUri());
    }
}
