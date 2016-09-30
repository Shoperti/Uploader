<?php

namespace Shoperti\Tests\Uploader;

class UploaderProcessorResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolversMayBeResolved()
    {
        $resolver = new \Shoperti\Uploader\Processors\ProcessorResolver();
        $resolver->register('foo', function () {
            return new \StdClass();
        });
        $result = $resolver->resolve('foo');
        $this->assertEquals(spl_object_hash($result), spl_object_hash($resolver->resolve('foo')));
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testResolverThrowsExceptionOnUnknownEngine()
    {
        $resolver = new \Shoperti\Uploader\Processors\ProcessorResolver();
        $resolver->resolve('foo');
    }
}
