<?php

namespace Shoperti\Tests\Uploader;

use Shoperti\Uploader\Contracts\Factory as FactoryContract;
use GrahamCampbell\Manager\AbstractManager;
use GrahamCampbell\TestBenchCore\ServiceProviderTrait;
use Shoperti\Uploader\Factory;

/**
 * This is the service provider test class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
class ServiceProviderTest extends AbstractTestCase
{
    use ServiceProviderTrait;

    public function testUploaderContractIsInjectable()
    {
        $this->assertIsInjectable(FactoryContract::class);
    }

    public function testBindings()
    {
        $this->assertInstanceOf(Factory::class, $this->app->make('uploader'));
        $this->assertInstanceOf(Factory::class, $this->app->make(FactoryContract::class));
    }
}
