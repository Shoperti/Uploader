<?php

namespace Shoperti\Uploader\NameGenerators;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Shoperti\Uploader\Contracts\NameGenerator;

/**
 * This is the fix name generator class.
 *
 * @author Arturo Rodríguez <arturo@shoperti.com>
 * @author Joseph Cohen <joe@shoperti.com>
 */
class FixNameGenerator implements NameGenerator
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

        $name = $this->getSanitizedFileName(Arr::get($pathInfo, 'filename'));
        $extension = Arr::get($pathInfo, 'extension');

        return "{$name}.{$extension}";
    }

    /**
     * Extracts and sanitizes the name (without extension) of the uploaded file.
     *
     * @param string $filename
     *
     * @return string
     */
    protected function getSanitizedFileName($filename)
    {
        return preg_replace('/_+/', '_', preg_replace("/[^a-z0-9_\-\.\,\+\*\(\)$']/i", '_', Str::ascii($filename)));
    }
}
