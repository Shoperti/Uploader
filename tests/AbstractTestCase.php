<?php

namespace Shoperti\Tests\Uploader;

use Shoperti\Uploader\UploaderServiceProvider;
use GrahamCampbell\TestBench\AbstractPackageTestCase;

/**
 * This is the abstract test case class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
abstract class AbstractTestCase extends AbstractPackageTestCase
{
    /**
     * Get the service provider class.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return string
     */
    protected function getServiceProviderClass($app)
    {
        return UploaderServiceProvider::class;
    }
}
