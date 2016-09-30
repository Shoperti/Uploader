<?php

namespace Shoperti\Uploader\NameGenerators;

/**
 * This is the name generator interface class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
interface NameGeneratorInterface
{
    /**
     * Generates a file name.
     *
     * @param  string $filePath
     * @param  array  $config
     *
     * @return sting
     */
    public function generate($filePath, array $config = []);
}
