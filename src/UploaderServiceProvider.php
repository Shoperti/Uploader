<?php

namespace Shoperti\Uploader;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Shoperti\Uploader\Contracts\Uploader as UploaderContract;

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
        $this->registerBindings($this->app);
    }

    /**
     * Register the Uploader class.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     */
    protected function registerBindings(Application $app)
    {
        $app->bind('uploader', function (Container $app) {
            $config = $app['config']['uploader'];
            $filesystemManager = $app['filesystem'];

            $configurationManager = new ConfigurationManager($config);
            $nameGenerator = new FileNameGenerator($filesystemManager);
            $fileProcessor = new FileProcessor();

            return new Uploader($configurationManager, $nameGenerator, $fileProcessor, $filesystemManager);
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
