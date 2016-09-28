<?php

namespace Shoperti\Tests\Uploader;

use Shoperti\Uploader\Contracts\Uploader as UploaderContract;
use GrahamCampbell\Manager\AbstractManager;
use GrahamCampbell\TestBenchCore\ServiceProviderTrait;
use Shoperti\Uploader\Uploader;

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
        $this->assertIsInjectable(UploaderContract::class);
    }

    public function testBindings()
    {
        $this->assertInstanceOf(Uploader::class, $this->app->make('uploader'));
        $this->assertInstanceOf(Uploader::class, $this->app->make(UploaderContract::class));
    }
}
