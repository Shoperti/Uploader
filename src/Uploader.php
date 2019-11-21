<?php

namespace Shoperti\Uploader;

use Exception;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Arr;
use Shoperti\Uploader\Contracts\Uploader as UploaderInterface;
use Shoperti\Uploader\Contracts\NameGenerator;
use Shoperti\Uploader\Contracts\FileProcessor;
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
     * The processor instance.
     *
     * @var \Shoperti\Uploader\Contracts\FileProcessor
     */
    protected $fileProcessor;

    /**
     * The filename generator instance.
     *
     * @var \Shoperti\Uploader\Contracts\NameGenerator
     */
    protected $nameGenerator;

    /**
     * The laravel filesystem instance.
     *
     * @var \Illuminate\Contracts\Filesystem\Factory
     */
    protected $filesystem;

    /**
     * The uploader config.
     *
     * @var array
     */
    protected $config;

    /**
     * Creates a new Uploader instance.
     *
     * @param \Shoperti\Uploader\Contracts\FileProcessor          $fileProcessor
     * @param \Illuminate\Contracts\Filesystem\Factory            $filesystem
     * @param \Shoperti\Uploader\Contracts\NameGenerator          $nameGenerator
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param array                                               $config
     *
     * @return void
     */
    public function __construct(
        FileProcessor $fileProcessor,
        NameGenerator $nameGenerator,
        FilesystemFactory $filesystem,
        UploadedFile $uploadedFile,
        array $config
    ) {
        $this->fileProcessor = $fileProcessor;
        $this->filesystem = $filesystem;
        $this->nameGenerator = $nameGenerator;
        $this->uploadedFile = $uploadedFile;
        $this->config = $config;
    }

    /**
     * Uploads a file to a filesystem disk.
     *
     * @param string      $path
     * @param string|null $disk
     *
     * @throws \Shoperti\Uploader\Exceptions\DisallowedFileException
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     * @throws \Exception
     *
     * @return \Shoperti\Uploader\UploadResult
     */
    public function upload($path = null, $disk = null)
    {
        // this may throw exceptions relative to the processor, like NotReadableException on image processor
        $processedFile = $this->fileProcessor->process($this->uploadedFile, $this->config);

        $basePath = implode(
            '/',
            array_filter([$path, Arr::get($this->config, 'subpath')])
        );

        $generatedFilename = $this->nameGenerator->generate($basePath.'/'.$this->uploadedFile->getClientOriginalName(), $this->config);

        $disk = $disk ?: Arr::get($this->config, 'disk');

        $uploadPath = $basePath.'/'.$generatedFilename;

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
            $generatedFilename,
            $url,
            $path['dirname'],
            [],
            $e
        );
    }

    /*
     * Uploads a file to a filesystem disk with a name.
     *
     * @param string      $path
     * @param string      $name
     * @param string|null $disk
     *
     * @throws \Shoperti\Uploader\Exceptions\DisallowedFileException
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     *
     * @return \Shoperti\Uploader\UploadResult
     */
    public function uploadAs($path, $name, $disk = null)
    {
        $processedFile = $this->fileProcessor->process($this->uploadedFile, $this->config);

        $filename = $this->nameGenerator->generate($this->uploadedFile, $this->config);

        $disk = $disk ?: Arr::get($this->config, 'disk');

        $uploadPath = implode(
            '/',
            array_filter([$path, Arr::get($this->config, 'subpath'), $filename])
        );

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
