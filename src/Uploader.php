<?php

namespace Shoperti\Uploader;

use ErrorException;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use League\Flysystem\FileNotFoundException as LeagueFileNotFoundException;
use Shoperti\Uploader\Contracts\Uploader as UploaderInterface;
use Shoperti\Uploader\Exceptions\FileNotFoundException;
use Shoperti\Uploader\Exceptions\RemoteFileException;
use Shoperti\Uploader\Processors\ProcessorResolver;
use Shoperti\Uploader\Processors\ProcessorInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Uploader.
 *
 * The upload files class.
 *
 * @author Arturo Rodríguez <arturo@shoperti.com>
 * @author Joseph Cohen <joe@shoperti.com>
 */
class Uploader implements UploaderInterface
{
    /**
     * The filename generator instance.
     *
     * @var \Shoperti\Uploader\FileNameGenerator
     */
    protected $nameGenerator;

    /**
     * The file processor instance.
     *
     * @var \Shoperti\Uploader\Processors\ProcessorResolver
     */
    protected $processors;

    /**
     * The laravel filesystem instance.
     *
     * @var \Illuminate\Contracts\Filesystem\Factory
     */
    protected $filesystem;

    /**
     * Creates a new Uploader instance.
     *
     * @param \Shoperti\Uploader\Factory                       $factory
     * @param \Shoperti\Uploader\Processors\ProcessorInterface $processor
     * @param \Illuminate\Contracts\Filesystem\Factory         $filesystem
     * @param \Shoperti\Uploader\FileNameGenerator             $nameGenerator
     * @param array                                            $config
     *
     * @return void
     */
    public function __construct(
        Factory $factory,
        ProcessorInterface $processor,
        FilesystemFactory $filesystem,
        FileNameGenerator $nameGenerator,
        $uploadedFile,
        array $config
    ) {
        $this->factory = $factory;
        $this->processor = $processor;
        $this->filesystem = $filesystem;
        $this->nameGenerator = $nameGenerator;
        $this->uploadedFile = $uploadedFile;
        $this->config = $config;
    }

    /**
     * Uploads a file to a filesystem disk.
     *
     * @param string                                                     $path
     * @param string|null                                                $disk
     *
     * @throws \Shoperti\Uploader\Exceptions\DisallowedFileException
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     *
     * @return \Shoperti\Uploader\UploadResult
     */
    public function upload($path, $disk = null)
    {
        $processedFile = $this->processor->process($this->uploadedFile, $this->config);

        $filename = $this->nameGenerator->generate($this->uploadedFile, $this->config);

        $disk = $disk ?: Arr::get($this->config, 'disk');

        $uploadPath = implode(array_filter([
            $path, Arr::get($this->config, 'subpath', ''), $filename
        ]), '/');

        try {
            // put() may throw an \InvalidArgumentException
            $wasMoved = $this->filesystem->disk($disk)->put($uploadPath, (string) $processedFile);
            $e = null;
        } catch (Exception $e) {
            $wasMoved = false;
        }

        $url = $wasMoved ? $this->filesystem->disk($disk)->url($uploadPath) : null;

        $path = pathinfo($uploadPath);

        return new UploadResult(
            $wasMoved,
            $this->uploadedFile,
            $disk,
            $filename,
            $url,
            $path['dirname'],
            [],
            $e
        );
    }

    /*
     * Uploads a file to a filesystem disk with a name.
     *
     * @param string                                                     $path
     * @param string                                                     $name
     * @param string|null                                                $disk
     *
     * @throws \Shoperti\Uploader\Exceptions\DisallowedFileException
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     *
     * @return \Shoperti\Uploader\UploadResult
     */
    public function uploadAs($path, $name, $disk = null)
    {
        $processedFile = $this->processor->process($this->uploadedFile, $this->config);

        $filename = $this->nameGenerator->generate($this->uploadedFile, $this->config);

        $disk = $disk ?: Arr::get($this->config, 'disk');

        $uploadPath = implode(array_filter([
            $path, Arr::get($this->config, 'subpath', ''), $filename
        ]), '/');

        try {
            // put() may throw an \InvalidArgumentException
            $wasMoved = $this->filesystem->disk($disk)->put($uploadPath, (string) $processedFile);
            $e = null;
        } catch (Exception $e) {
            $wasMoved = false;
        }

        $url = $wasMoved ? $this->filesystem->disk($disk)->url($uploadPath) : null;

        $path = pathinfo($uploadPath);

        return new UploadResult(
            $wasMoved,
            $this->uploadedFile,
            $disk,
            $filename,
            $url,
            $path['dirname'],
            [],
            $e
        );
    }
}
