<?php

namespace Shoperti\Uploader;

use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Shoperti\Uploader\Exceptions\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FileNameGenerator.
 *
 * Class for generating file names.
 *
 * @author Arturo Rodríguez <arturo@shoperti.com>
 */
class FileNameGenerator
{
    use HandlesFilesTrait;

    /**
     * The Laravel storage instance.
     *
     * @var \Illuminate\Filesystem\FilesystemManager
     */
    protected $filesystemManager;

    /**
     * Creates a new file name generator instance.
     *
     * @param $filesystemManager
     */
    public function __construct(FilesystemManager $filesystemManager)
    {
        $this->filesystemManager = $filesystemManager;
    }

    /**
     * Gets the file name from the uploaded file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param array                                               $config
     *
     * @throws \Shoperti\Uploader\Exceptions\InvalidConfigurationException
     *
     * @return string
     */
    public function generate(UploadedFile $uploadedFile, $config)
    {
        $namingStrategy = Arr::get($config, 'file_naming');

        if (!in_array($namingStrategy, ['none', 'uniqid', 'fix', 'fix_unique'])) {
            throw new InvalidConfigurationException('Invalid naming strategy');
        }

        if ('uniqid' === $namingStrategy) {
            return str_replace('.', '-', uniqid('', true)).'.'.$this->getFileExtension($uploadedFile);
        }

        if ('fix' === $namingStrategy) {
            return "{$this->getSanitizedFileName($uploadedFile)}.{$this->getFileExtension($uploadedFile)}";
        }

        if ('fix_unique' === $namingStrategy) {
            $disk = Arr::get($config, 'disk');

            $name = $this->getSanitizedFileName($uploadedFile);
            $ext = $this->getFileExtension($uploadedFile);
            $subpath = $configurationManager->subpathSetting();

            $filePath = "{$subpath}{$name}.{$ext}";

            if ($this->filesystemManager->disk($disk)->exists($filePath)) {
                $suffix = 0;
                do {
                    $newName = $name.(++$suffix);
                    $filePath = "{$subpath}{$newName}.{$ext}";
                } while ($this->filesystemManager->disk($disk)->exists($filePath));
                $name = $newName;
            }

            return "{$name}.{$ext}";
        }

        return $uploadedFile->getClientOriginalName();
    }

    /**
     * Extracts and sanitizes the name (without extension) of the uploaded file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     *
     * @return mixed
     */
    protected function getSanitizedFileName(UploadedFile $uploadedFile)
    {
        $name = Str::ascii($this->getFileName($uploadedFile));

        return preg_replace('/_+/', '_', preg_replace("/[^a-z0-9_\-\.\,\+\*\(\)$']/i", '_', $name));
    }
}
