<?php

namespace Shoperti\Uploader;

use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Shoperti\Uploader\Contracts\UploaderManager as UploaderManagerContract;
use Shoperti\Uploader\FileProcessors\ProcessorResolver;
use Shoperti\Uploader\FileProcessors\GenericFileProcessor;
use Shoperti\Uploader\FileProcessors\ImageFileProcessor;
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
        $this->registerNameGeneratorResolver();
        $this->registerProcessorResolver();
        $this->registerBindings();
    }

    /**
     * Registers the uploader processor resolver.
     *
     * @return void
     */
    public function registerNameGeneratorResolver()
    {
        $this->app->singleton('uploader.namegenerator.resolver', function (Container $app) {
            $resolver = new NameGeneratorResolver();

            $resolver->register('none', new NoneNameGenerator());
            $resolver->register('uniqid', new UniqidNameGenerator());
            $resolver->register('fix', new FixNameGenerator());
            $resolver->register('fix_unique', new FixUniqueNameGenerator($app['filesystem']));

            return $resolver;
        });
    }

    /**
     * Registers the uploader processor resolver.
     *
     * @return void
     */
    public function registerProcessorResolver()
    {
        $this->app->singleton('uploader.processor.resolver', function (Container $app) {
            $resolver = new ProcessorResolver();

            $resolver->register('file', function () {
                return new GenericFileProcessor();
            });

            $resolver->register('image', function () {
                return new ImageFileProcessor();
            });

            return $resolver;
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
            $nameGeneratorResolver = $app['uploader.namegenerator.resolver'];
            $filesystem = $app['filesystem'];

            return new UploaderManager($processorResolver, $nameGeneratorResolver, $filesystem, $config);
        });

        $this->app->alias('uploader', UploaderManager::class);
        $this->app->alias('uploader', UploaderManagerContract::class);
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
