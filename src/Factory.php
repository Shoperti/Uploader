<?php

namespace Shoperti\Uploader;

use ErrorException;
use Exception;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use League\Flysystem\FileNotFoundException as LeagueFileNotFoundException;
use Shoperti\Uploader\Contracts\Factory as FactoryContract;
use Shoperti\Uploader\Exceptions\FileNotFoundException;
use Shoperti\Uploader\Exceptions\RemoteFileException;
use Shoperti\Uploader\Processors\ProcessorResolver;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Shoperti\Uploader\Exceptions\DisallowedFileException;
use Shoperti\Uploader\Exceptions\InvalidConfigurationException;
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
class Factory implements FactoryContract
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

    protected $config;

    /**
     * Creates a new Uploader instance.
     *
     * @param \Shoperti\Uploader\Processors\ProcessorResolver  $processors
     * @param \Illuminate\Contracts\Filesystem\Factory         $filesystem
     * @param \Shoperti\Uploader\FileNameGenerator             $nameGenerator
     *
     * @return void
     */
    public function __construct(
        ProcessorResolver $processors,
        FilesystemFactory $filesystem,
        FileNameGenerator $nameGenerator,
        array $config
    ) {
        $this->processors = $processors;
        $this->filesystem = $filesystem;
        $this->nameGenerator = $nameGenerator;
        $this->config = $config;
    }

    public function make($uploadedFile)
    {
        $file = $this->getFile($uploadedFile);

        $config = $this->getConfigFromFile($uploadedFile);

        $processor = $this->processors->resolve(Arr::get($config, 'processor'));

        return new Uploader(
            $this,
            $processor,
            $this->filesystem,
            $this->nameGenerator,
            $file,
            $config
        );
    }

    public function getFile($file)
    {
        if (!is_string($file)) {
            return $file;
        }

        // this may throw a RemoteFileException
        return $this->getFileFromUrl($file);
    }

    /**
     * Gets a file stored in a remote location, accessible through HTTP.
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
     * @param string $filePath
     * @param string $disk
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
     * @return void
     */
    public function getConfigFromFile($uploadedFile)
    {
        if (! $fileMimeType = $this->getMimeTypeFromAllowedFile($uploadedFile)) {
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
     * @throws \Shoperti\Uploader\Exceptions\DisallowedFileException
     *
     * @return string
     */
    protected function getMimeTypeFromAllowedFile($uploadedFile)
    {
        $fileMimeType = $uploadedFile->getMimeType();

        if (!in_array($fileMimeType, Arr::get($this->config, 'blocked_mimetypes', []))) {
            return $fileMimeType;
        }

        return false;
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
