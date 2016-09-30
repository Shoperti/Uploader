<?php

namespace Shoperti\Uploader\NameGenerators;

/**
 * This is the none name generator class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
class NoneNameGenerator implements NameGeneratorInterface
{
    /**
     * Generates a file name.
     *
     * @param  string $filePath
     * @param  array  $config
     *
     * @return sting
     */
    public function generate($filePath, array $config = [])
    {
        return basename($filePath);
    }
}
