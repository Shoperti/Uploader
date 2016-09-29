<?php

namespace Shoperti\Uploader;

use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Shoperti\Uploader\Contracts\Uploader as UploaderContract;
use Shoperti\Uploader\Contracts\Factory as FactoryContract;
use Shoperti\Uploader\Processors\ProcessorResolver;
use Shoperti\Uploader\Processors\FileProcessor;
use Shoperti\Uploader\Processors\ImageProcessor;
use Shoperti\Uploader\ConfigManager;
use Laravel\Lumen\Application as LumenApplication;

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

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('uploader.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('uploader');
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
        // $this->registerConfigManager();
        $this->registerProcessorResolver();
        $this->registerBindings();
    }

    public function registerConfigManager()
    {
        $this->app->singleton('uploader.config.manager', function (Container $app) {
            return new ConfigManager($app['config']['uploader']);
        });
    }

    public function registerProcessorResolver()
    {
        $this->app->singleton('uploader.processor.resolver', function (Container $app) {
            $resolver = new ProcessorResolver();

            foreach (['file', 'image'] as $processor) {
                $this->{'register'.ucfirst($processor).'Processor'}($resolver, $processor);
            }

            return $resolver;
        });
    }

    /**
     * Register the files processor implementation.
     *
     * @param \Shoperti\Uploader\Processors\ProcessorResolver $resolver
     * @param string                                          $processor
     *
     * @return void
     */
    public function registerFileProcessor(ProcessorResolver $resolver, $processor)
    {
        $resolver->register($processor, function () {
            return new FileProcessor();
        });
    }

    /**
     * Register the images processor implementation.
     *
     * @param \Shoperti\Uploader\Processors\ProcessorResolver $resolver
     * @param string                                          $processor
     *
     * @return void
     */
    public function registerImageProcessor(ProcessorResolver $resolver, $processor)
    {
        $resolver->register($processor, function () {
            return new ImageProcessor();
        });
    }

    /**
     * Register the Uploader class.
     *
     * @return void
     */
    protected function registerBindings()
    {
        $this->app->singleton('uploader', function (Container $app) {
            $config = $app['config']['uploader'];

            $resolver = $app['uploader.processor.resolver'];

            $filesystem = $app['filesystem'];

            $nameGenerator = new FileNameGenerator($filesystem);

            return new Factory($resolver, $filesystem, $nameGenerator, $config);
        });

        $this->app->alias('uploader', Factory::class);
        $this->app->alias('uploader', FactoryContract::class);
    }

    // /**
    //  * Get the services provided by the provider.
    //  *
    //  * @return string[]
    //  */
    // public function provides()
    // {
    //     return [
    //         'uploader',
    //     ];
    // }
}
