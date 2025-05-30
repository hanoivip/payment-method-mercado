<?php

namespace Hanoivip\PaymentMethodMercado;

use Illuminate\Support\ServiceProvider;

class LibServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../lang' => resource_path('lang/vendor/hanoivip'),
            __DIR__.'/../config' => config_path(),
        ]);
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadTranslationsFrom( __DIR__.'/../lang', 'hanoivip.mercado');
        $this->mergeConfigFrom( __DIR__.'/../config/mercado.php', 'mercado');
        $this->loadViewsFrom(__DIR__ . '/../views', 'hanoivip.mercado');
    }
    
    public function register()
    {
        $this->commands([
        ]);
        $this->app->bind("MercadoPaymentMethod", MercadoMethod::class);
        $this->app->bind(IHelper::class, Helper::class);
    }
}
