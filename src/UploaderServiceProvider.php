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
use Shoperti\Uploader\NameGenerators\FixNameGenerator;
use Shoperti\Uploader\NameGenerators\FixUniqueNameGenerator;
use Shoperti\Uploader\NameGenerators\NameGeneratorResolver;
use Shoperti\Uploader\NameGenerators\NoneNameGenerator;
use Shoperti\Uploader\NameGenerators\UniqidNameGenerator;
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
     * Registers any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerNameGeneratosResolver();
        $this->registerProcessorResolver();
        $this->registerBindings();
    }

    /**
     * Registers the uplaoder processor resolver.
     *
     * @return void
     */
    public function registerNameGeneratosResolver()
    {
        $this->app->singleton('uploader.namegeneratos.resolver', function (Container $app) {
            $resolver = new NameGeneratorResolver();

            $resolver->register('none', new NoneNameGenerator());
            $resolver->register('uniqid', new UniqidNameGenerator());
            $resolver->register('fix', new FixNameGenerator());
            $resolver->register('fix_unique', new FixUniqueNameGenerator($app['filesystem']));

            return $resolver;
        });
    }

    /**
     * Registers the uplaoder processor resolver.
     *
     * @return void
     */
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
     * Registers the files processor implementation.
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
     * Registers the images processor implementation.
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
     * Registers the uploader class.
     *
     * @return void
     */
    protected function registerBindings()
    {
        $this->app->singleton('uploader', function (Container $app) {
            $config = $app['config']['uploader'];
            $processorResolver = $app['uploader.processor.resolver'];
            $nameGeneratorResolver = $app['uploader.namegeneratos.resolver'];
            $filesystem = $app['filesystem'];

            return new Factory($processorResolver, $nameGeneratorResolver, $filesystem, $config);
        });

        $this->app->alias('uploader', Factory::class);
        $this->app->alias('uploader', FactoryContract::class);
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
