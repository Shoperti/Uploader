<?php

namespace Shoperti\Uploader\Contracts;

/**
 * This is the name generator interface class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
interface NameGenerator
{
    /**
     * Generates a file name.
     *
     * @param string $filePath
     * @param array  $config
     *
     * @return string
     */
    public function generate($filePath, array $config = []);
}
