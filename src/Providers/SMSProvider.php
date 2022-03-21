<?php

namespace Cccdz\Sms\Providers;

use Cccdz\Sms\SMSManager;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class SMSProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        $path = realpath(__DIR__.'/../../config/sms.php');

        $this->publishes([$path => config_path('sms.php')]);

        $this->mergeConfigFrom($path, 'sms');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerAliases();

        $this->registerSMS();
    }

    public function registerSMS()
    {
        $this->app->singleton('SMS', function ($app) {

            $config = $app->make('config')->get('sms', []);

            return new SMSManager($app, $config['default'], $config['connections']);
        });
    }

    public function registerAliases()
    {
        $this->app->alias('SMS', SMSManager::class);
    }
}