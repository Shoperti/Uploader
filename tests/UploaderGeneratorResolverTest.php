<?php

namespace Shoperti\Tests\Uploader;

use Shoperti\Uploader\Contracts\NameGenerator;
use Shoperti\Uploader\NameGenerators\NameGeneratorResolver;

class UploaderGeneratorResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolversMayBeResolved()
    {
        $resolver = new NameGeneratorResolver();
        $resolver->register('foo', new TestNameGenerator());
        $result = $resolver->resolve('foo');
        $this->assertEquals(spl_object_hash($result), spl_object_hash($resolver->resolve('foo')));
    }
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testResolverThrowsExceptionOnUnknownEngine()
    {
        $resolver = new NameGeneratorResolver();
        $resolver->resolve('foo');
    }
}

class TestNameGenerator implements NameGenerator
{
    public function generate($filePath, array $config = [])
    {
        //
    }
}
