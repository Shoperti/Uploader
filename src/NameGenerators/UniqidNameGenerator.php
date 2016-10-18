<?php

namespace Shoperti\Uploader\NameGenerators;

use Illuminate\Support\Arr;
use Shoperti\Uploader\Contracts\NameGenerator;

/**
 * This is the uniqid generator class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
class UniqidNameGenerator implements NameGenerator
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
        $pathInfo = pathinfo($filePath);

        return str_replace('.', '-', uniqid('', true)).'.'.Arr::get($pathInfo, 'extension');
    }
}
