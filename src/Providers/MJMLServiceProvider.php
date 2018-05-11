<?php

namespace Asahasrabuddhe\LaravelMJML\Providers;

use Illuminate\Support\ServiceProvider;

class MJMLServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/mjml.php' => config_path('mjml.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mjml.php',
            'mjml'
        );
    }
}
