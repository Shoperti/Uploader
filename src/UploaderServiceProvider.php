<?php

namespace Shoperti\Uploader;

use Illuminate\Contracts\Container\Container;
use Shoperti\Uploader\Contracts\Uploader as UploaderContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class UploaderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
    }

    /**
     * Setup the config.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../config/uploader.php');

        if (class_exists('Illuminate\Foundation\Application', false)) {
            $this->publishes([$source => config_path('uploader.php')]);
        }

        $this->mergeConfigFrom($source, 'uploader');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // $this->registerFactory($this->app);

        $this->registerBindings($this->app);
    }

    // /**
    //  * Register the factory class.
    //  *
    //  * @param \Illuminate\Contracts\Foundation\Application $app
    //  *
    //  * @return void
    //  */
    // protected function registerFactory(Application $app)
    // {
    //     $app->singleton('server.factory', function ($app) {
    //         return new UploaderFactory($app['filesystem']);
    //     });
    //
    //     $app->alias('uploader.factory', UploaderFactory::class);
    // }

    /**
     * Register the glide class.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     */
    protected function registerBindings(Application $app)
    {
        $app->bind('uploader', function (Container $app) {
            $config = $app['config']['uploader'];
            $filesystem = $app['files'];
            $storage = $app['filesystem'];

            return new Uploader($config, $filesystem, $storage);
        });

        $app->alias('uploader', UploaderContract::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'uploader',
        ];
    }
}
