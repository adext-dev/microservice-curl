<?php

namespace Adext\Curl;

use Adext\Curl\Http\Contracts\HttpClient as HttpClientContract;
use Adext\Curl\Http\Facade;
use Adext\Curl\Http\HttpClient;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class CurlServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Facade::setHttpClient($this->app[HttpClientContract::class]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(HttpClient::class, function ($app) {

            return new HttpClient(
                new Client
            );
        });
        $this->app->alias(HttpClient::class, HttpClientContract::class);
    }
}
