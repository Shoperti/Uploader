<?php

namespace Shoperti\Tests\Uploader;

use PHPUnit\Framework\TestCase;
use Shoperti\Uploader\FileProcessors\ProcessorResolver;

class UploaderProcessorResolverTest extends TestCase
{
    public function testResolversMayBeResolved()
    {
        $resolver = new ProcessorResolver();
        $resolver->register('foo', function () {
            return new \StdClass();
        });
        $result = $resolver->resolve('foo');
        $this->assertEquals(spl_object_hash($result), spl_object_hash($resolver->resolve('foo')));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testResolverThrowsExceptionOnUnknownEngine()
    {
        $resolver = new ProcessorResolver();
        $resolver->resolve('foo');
    }
}
