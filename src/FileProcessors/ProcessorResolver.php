<?php

namespace Shoperti\Uploader\FileProcessors;

use Closure;
use InvalidArgumentException;

/**
 * This is the processor resolver class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
class ProcessorResolver
{
    /**
     * The array of processor resolvers.
     *
     * @var array
     */
    protected $resolvers = [];

    /**
     * The resolved processor instances.
     *
     * @var array
     */
    protected $resolved = [];

    /**
     * Registers a new processor resolver.
     *
     * The processor string typically corresponds to a file extension.
     *
     * @param string   $processor
     * @param \Closure $resolver
     *
     * @return void
     */
    public function register($processor, Closure $resolver)
    {
        unset($this->resolved[$processor]);

        $this->resolvers[$processor] = $resolver;
    }

    /**
     * Resolvers an processor instance by name.
     *
     * @param string $processor
     *
     * @throws \InvalidArgumentException
     *
     * @return \Shoperti\Uploader\Contracts\FileProcessor
     */
    public function resolve($processor)
    {
        if (isset($this->resolved[$processor])) {
            return $this->resolved[$processor];
        }

        if (isset($this->resolvers[$processor])) {
            return $this->resolved[$processor] = call_user_func($this->resolvers[$processor]);
        }

        throw new InvalidArgumentException("Processor $processor not found.");
    }
}
