<?php

namespace Shoperti\Uploader\NameGenerators;

use Shoperti\Uploader\Contracts\NameGenerator;

/**
 * This is the none name generator class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
class NoneNameGenerator implements NameGenerator
{
    /**
     * Generates a file name.
     *
     * @param string $filePath
     * @param array  $config
     *
     * @return string
     */
    public function generate($filePath, array $config = [])
    {
        return basename($filePath);
    }
}
