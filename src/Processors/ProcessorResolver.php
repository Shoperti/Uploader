<?php

namespace Shoperti\Uploader\Processors;

use Closure;
use InvalidArgumentException;

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
     * Register a new processor resolver.
     *
     * The processor string typically corresponds to a file extension.
     *
     * @param  string   $processor
     * @param  \Closure  $resolver
     *
     * @return void
     */
    public function register($processor, Closure $resolver)
    {
        unset($this->resolved[$processor]);

        $this->resolvers[$processor] = $resolver;
    }

    /**
     * Resolver an processor instance by name.
     *
     * @param  string  $processor
     *
     * @throws \InvalidArgumentException
     *
     * @return \Shoperti\Uploader\Processors\ProcessorInterface
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
