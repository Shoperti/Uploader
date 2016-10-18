<?php

namespace Shoperti\Tests\Uploader;

use Shoperti\Uploader\Contracts\UploaderManager as UploaderManagerContract;
use GrahamCampbell\TestBenchCore\ServiceProviderTrait;
use Shoperti\Uploader\UploaderManager;

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
        $this->assertIsInjectable(UploaderManagerContract::class);
    }

    public function testBindings()
    {
        $this->assertInstanceOf(UploaderManager::class, $this->app->make('uploader'));
        $this->assertInstanceOf(UploaderManager::class, $this->app->make(UploaderManagerContract::class));
    }
}
