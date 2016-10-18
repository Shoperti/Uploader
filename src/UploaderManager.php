<?php

namespace Shoperti\Uploader;

use ErrorException;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use League\Flysystem\FileNotFoundException as LeagueFileNotFoundException;
use Shoperti\Uploader\Contracts\UploaderManager as UploaderManagerContract;
use Shoperti\Uploader\Exceptions\FileNotFoundException;
use Shoperti\Uploader\Exceptions\RemoteFileException;
use Shoperti\Uploader\FileProcessors\ProcessorResolver;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Shoperti\Uploader\Exceptions\DisallowedFileException;
use Shoperti\Uploader\Exceptions\InvalidConfigurationException;
use Shoperti\Uploader\NameGenerators\NameGeneratorResolver;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class Uploader.
 *
 * The upload files class.
 *
 * @author Arturo Rodríguez <arturo@shoperti.com>
 * @author Joseph Cohen <joe@shoperti.com>
 */
class UploaderManager implements UploaderManagerContract
{
    /**
     * The filename generator instance.
     *
     * @var \Shoperti\Uploader\NameGenerators\NameGeneratorResolver
     */
    protected $generators;

    /**
     * The file processor instance.
     *
     * @var \Shoperti\Uploader\FileProcessors\ProcessorResolver
     */
    protected $processors;

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
     * Creates a new uploader manager instance.
     *
     * @param \Shoperti\Uploader\FileProcessors\ProcessorResolver     $processors
     * @param \Shoperti\Uploader\NameGenerators\NameGeneratorResolver $generators
     * @param \Illuminate\Contracts\Filesystem\Factory                $filesystem
     * @param array                                                   $config
     *
     * @return void
     */
    public function __construct(
        ProcessorResolver $processors,
        NameGeneratorResolver $generators,
        FilesystemFactory $filesystem,
        array $config
    ) {
        $this->processors = $processors;
        $this->generators = $generators;
        $this->filesystem = $filesystem;
        $this->config = $config;
    }

    /**
     * Makes a new uploader instance.
     *
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param  string|null                                         $connection
     *
     * @return \Shoperti\Uploader\Uploader
     */
    public function make($uploadedFile, $connection = null)
    {
        $file = $this->getFile($uploadedFile);

        $config = $connection
            ? Arr::get($this->config['configurations'], $connection)
            : $this->getConfigFromFile($uploadedFile);

        $fileProcessor = $this->processors->resolve(Arr::get($config, 'processor'));

        $nameGenerator = $this->generators->resolve(Arr::get($config, 'name_generator', 'none'));

        return new Uploader(
            $fileProcessor,
            $nameGenerator,
            $this->filesystem,
            $file,
            $config
        );
    }

    /**
     * Gets a file to process.
     *
     * @param  string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getFile($file)
    {
        return is_string($file) ? $this->getFileFromUrl($file) : $file;
    }

    /**
     * Gets a file stored in a remote location accessible through HTTP.
     *
     * @param string $url
     *
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getFileFromUrl($url)
    {
        $encodedUrl = str_replace(' ', '%20', $url);
        $tmpFileName = tempnam(sys_get_temp_dir(), 's-down-');

        try {
            file_put_contents($tmpFileName, fopen($encodedUrl, 'r'));
        } catch (ErrorException $e) {
            throw new RemoteFileException($encodedUrl, sprintf("Unable to get file from '%s'", $encodedUrl));
        }

        /** @var \Symfony\Component\HttpFoundation\File\File $file */
        $file = new File($tmpFileName, false);

        $fileName = $this->getRemoteFileName($http_response_header, $url);

        /* @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
        return new UploadedFile($tmpFileName, $fileName, $file->getMimeType(), $file->getSize());
    }

    /**
     * Deletes a stored file.
     *
     * @param string $disk
     * @param string $filePath
     *
     * @throws \Shoperti\Uploader\Exceptions\FileNotFoundException
     *
     * @return bool true on success, false on failure.
     */
    public function delete($disk, $filePath)
    {
        try {
            return $this->filesystem->disk($disk)->delete($filePath);
        } catch (LeagueFileNotFoundException $e) {
            throw new FileNotFoundException(
                $e->getPath(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Gets the name from a downloaded remote file searching int the headers and falling back using the URL.
     *
     * @param array  $headers
     * @param string $url
     *
     * @return string
     */
    protected function getRemoteFileName($headers, $url)
    {
        // try to see if the server returned the file name to save
        foreach ($headers as $header) {
            if (strpos(strtolower($header), 'content-disposition') !== false) {
                $name = explode('=', $header);
                if (isset($name[1])) {
                    return trim($name[1], '";\'');
                }
            }
        }

        // get the name from the url
        $name = preg_replace('/\\?.*/', '', $url);

        if (false !== ($pos = strpos($name, '#'))) {
            $name = substr($name, 0, $pos);
        }

        return str_replace(' ', '-', basename($name));
    }

    /**
     * Resolves the configuration to use according to the uploaded file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     *
     * @throws \Shoperti\Uploader\Exceptions\DisallowedFileException
     * @throws \Shoperti\Uploader\Exceptions\InvalidConfigurationException
     *
     * @return array
     */
    protected function getConfigFromFile($uploadedFile)
    {
        if (!$fileMimeType = $this->getMimeTypeFromAllowedFile($uploadedFile)) {
            throw new DisallowedFileException(
                $uploadedFile->getClientOriginalName(),
                $fileMimeType,
                sprintf("File with not allowed mime-type '%s'", $fileMimeType)
            );
        }

        return $this->getConfigForMimeType($fileMimeType);
    }

    /**
     * Gets the file mime-type if it's part of the configured allowed mime-types.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     *
     * @return string|null
     */
    protected function getMimeTypeFromAllowedFile($uploadedFile)
    {
        $fileMimeType = $uploadedFile->getMimeType();

        return !in_array($fileMimeType, Arr::get($this->config, 'blocked_mimetypes', []))
            ? $fileMimeType
            : null;
    }

    /**
     * Obtains the corresponding connection configuration depending on the file mime-type.
     *
     * @param string $fileMimeType
     *
     * @throws \Shoperti\Uploader\Exceptions\InvalidConfigurationException
     *
     * @return array
     */
    protected function getConfigForMimeType($fileMimeType)
    {
        $processorConfig = Collection::make(Arr::get($this->config, 'mime_resolvers', []))
            ->filter(function ($mimes) use ($fileMimeType) {
                return Collection::make($mimes)->contains(function ($mime) use ($fileMimeType) {
                    return Str::is($mime, $fileMimeType);
                });
            })->keys()->first();

        if ($config = Arr::get($this->config['configurations'], $processorConfig)) {
            return $config;
        }

        throw new InvalidConfigurationException(sprintf("No configuration found for '%s'", $fileMimeType));
    }
}
