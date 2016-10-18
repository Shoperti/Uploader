<?php

namespace Shoperti\Uploader\NameGenerators;

use InvalidArgumentException;

/**
 * This is the name generator resolver class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
class NameGeneratorResolver
{
    /**
     * The array of generator resolvers.
     *
     * @var array
     */
    protected $resolvers = [];

    /**
     * The resolved generator instances.
     *
     * @var array
     */
    protected $resolved = [];

    /**
     * Registers a new name generator resolver.
     *
     * @param string                                     $generator
     * @param \Shoperti\Uploader\Contracts\NameGenerator $resolver
     *
     * @return void
     */
    public function register($generator, $resolver)
    {
        unset($this->resolved[$generator]);

        $this->resolvers[$generator] = $resolver;
    }

    /**
     * Resolves a generator instance by name.
     *
     * @param string $generator
     *
     * @throws \InvalidArgumentException
     *
     * @return \Shoperti\Uploader\Contracts\NameGenerator
     */
    public function resolve($generator)
    {
        if (isset($this->resolved[$generator])) {
            return $this->resolved[$generator];
        }

        if (isset($this->resolvers[$generator])) {
            return $this->resolved[$generator] = $this->resolvers[$generator];
        }

        throw new InvalidArgumentException("Name generator $generator not found.");
    }
}
