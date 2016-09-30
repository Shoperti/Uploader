<?php

namespace Shoperti\Uploader\NameGenerators;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * This is the fix unique name generator class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
class FixUniqueNameGenerator implements NameGeneratorInterface
{
    /**
     * The laravel storage instance.
     *
     * @var \Illuminate\Contracts\Filesystem\Factory
     */
    protected $filesystem;

    /**
     * Creates a new file name generator instance.
     *
     * @param \Illuminate\Contracts\Filesystem\Factory $filesystem
     *
     * @return void
     */
    public function __construct(FilesystemFactory $filesystem)
    {
        $this->filesystem = $filesystem;
    }

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

        $disk = Arr::get($config, 'disk');

        $name = $this->getSanitizedFileName(Arr::get($pathInfo, 'filename'));
        $extension = Arr::get($pathInfo, 'extension');
        $path = Arr::get($pathInfo, 'dirname');

        $filePath = "{$path}/{$name}.{$extension}";

        if ($this->filesystem->disk($disk)->exists($filePath)) {
            $suffix = 0;

            do {
                $newName = $name.'-'.(++$suffix);
                $filePath = "{$path}/{$newName}.{$extension}";
            } while ($this->filesystem->disk($disk)->exists($filePath));

            $name = $newName;
        }

        return "{$name}.{$extension}";
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
