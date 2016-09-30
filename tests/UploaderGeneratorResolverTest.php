<?php

namespace Shoperti\Tests\Uploader;

class UploaderGeneratorResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolversMayBeResolved()
    {
        $resolver = new \Shoperti\Uploader\NameGenerators\NameGeneratorResolver();
        $resolver->register('foo', new \StdClass());
        $result = $resolver->resolve('foo');
        $this->assertEquals(spl_object_hash($result), spl_object_hash($resolver->resolve('foo')));
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testResolverThrowsExceptionOnUnknownEngine()
    {
        $resolver = new \Shoperti\Uploader\NameGenerators\NameGeneratorResolver();
        $resolver->resolve('foo');
    }
}
