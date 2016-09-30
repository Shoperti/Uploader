<?php

namespace Shoperti\Uploader\NameGenerators;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * This is the fix name generator class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
class FixNameGenerator implements NameGeneratorInterface
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
        $pathInfo = pathinfo($filePath);

        return $this->getSanitizedFileName(Arr::get($pathInfo, 'filename')).Arr::get($pathInfo, 'extension');
    }

    /**
     * Extracts and sanitizes the name (without extension) of the uploaded file.
     *
     * @param string $filename
     *
     * @return string
     */
    protected function getSanitizedFileName($filenane)
    {
        return preg_replace('/_+/', '_', preg_replace("/[^a-z0-9_\-\.\,\+\*\(\)$']/i", '_', Str::ascii($filenane)));
    }
}
