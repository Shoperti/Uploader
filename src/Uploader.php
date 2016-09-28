<?php

namespace Shoperti\Uploader;

use ErrorException;
use Exception;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Intervention\Image\Image;
use League\Flysystem\FileNotFoundException as LeagueFileNotFoundException;
use Shoperti\Uploader\Contracts\Uploader as UploaderInterface;
use Shoperti\Uploader\Exceptions\FileNotFoundException;
use Shoperti\Uploader\Exceptions\RemoteFileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Uploader.
 *
 * The upload files class.
 *
 * @author Arturo Rodríguez <arturo@shoperti.com>
 */
class Uploader implements UploaderInterface
{
    /**
     * The configuration manager instance.
     *
     * @var \Shoperti\Uploader\ConfigurationManager
     */
    protected $configurationManager;

    /**
     * The filename generator instance.
     *
     * @var \Shoperti\Uploader\FileNameGenerator
     */
    protected $fileNameGenerator;

    /**
     * The file processor instance.
     *
     * @var \Shoperti\Uploader\FileProcessor
     */
    protected $fileProcessor;

    /**
     * Laravel storage instance.
     *
     * @var \Illuminate\Contracts\Filesystem\Factory
     */
    protected $filesystemFactory;

    /**
     * Creates a new Uploader instance.
     *
     * @param \Shoperti\Uploader\ConfigurationManager  $configurationManager
     * @param \Shoperti\Uploader\FileNameGenerator     $fileNameGenerator
     * @param \Shoperti\Uploader\FileProcessor         $fileProcessor
     * @param \Illuminate\Contracts\Filesystem\Factory $filesystemFactory
     *
     * @return void
     */
    public function __construct(
        ConfigurationManager $configurationManager,
        FileNameGenerator $fileNameGenerator,
        FileProcessor $fileProcessor,
        FilesystemFactory $filesystemFactory
    ) {
        $this->configurationManager = $configurationManager;
        $this->fileNameGenerator = $fileNameGenerator;
        $this->fileProcessor = $fileProcessor;
        $this->filesystemFactory = $filesystemFactory;
    }

    /**
     * Uploads the file to the storage filesystem.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $resourceFile
     *
     * @throws \Shoperti\Uploader\Exceptions\DisallowedFileException
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     *
     * @return \Shoperti\Uploader\UploadResult
     */
    public function upload($resourceFile)
    {
        if (is_string($resourceFile)) {
            // this may throw a RemoteFileException
            $resourceFile = $this->getFileFromUrl($resourceFile);
        }

        // may throw a DisallowedFileException, which should be caught on implementation
        // may throw an InvalidConfigurationException, which indicates a missconfiguration
        $this->configurationManager->resolve($resourceFile);

        list($file, $attributes) = $this->fileProcessor->process($resourceFile, $this->configurationManager);

        $name = $this->fileNameGenerator->generate($resourceFile, $this->configurationManager);

        try {
            $wasMoved = $this->moveToStorage($file, $name);
            $e = null;
        } catch (Exception $e) {
            $wasMoved = false;
        }

        $url = $wasMoved ?
            $this->filesystemFactory->disk($this->configurationManager->setting('disk'))->url(
                "{$this->configurationManager->subpathSetting()}{$name}"
            )
            : null;

        return new UploadResult(
            $wasMoved,
            $resourceFile,
            $url,
            $this->configurationManager->getResolvedConfiguration(),
            $name,
            $attributes,
            $e
        );
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

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
        $uploadedFile = new UploadedFile($tmpFileName, $fileName, $file->getMimeType(), $file->getSize());

        return $uploadedFile;
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
            return $this->filesystemFactory->disk($disk)->delete($filePath);
        } catch (LeagueFileNotFoundException $e) {
            throw new FileNotFoundException(
                $e->getPath(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Moves the uploaded file to the filesystem disk location.
     *
     * @param \Illuminate\Filesystem\Filesystem|Image $file
     * @param string                                  $filename
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    protected function moveToStorage($file, $filename)
    {
        $disk = $this->configurationManager->setting('disk');
        $uploadPath = "{$this->configurationManager->subpathSetting()}{$filename}";

        // put() may throw an \InvalidArgumentException
        return $this->filesystemFactory->disk($disk)->put($uploadPath, (string) $file);
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
}
