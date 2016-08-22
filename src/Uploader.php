<?php

namespace Shoperti\Uploader;

use Shoperti\Uploader\Contracts\Uploader as UploaderInterface;
use Shoperti\Uploader\Exceptions\DisallowedFileException;
use Shoperti\Uploader\Exceptions\FileNotFoundException;
use Shoperti\Uploader\Exceptions\RemoteFileException;
use ErrorException;
use Exception;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;
use League\Flysystem\FileNotFoundException as LeagueFileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use UnexpectedValueException;

/**
 * The Uploader class.
 */
class Uploader implements UploaderInterface
{
    /**
     * The full Wingu configuration settings.
     *
     * @var array
     */
    protected $config;

    /**
     * Laravel filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * Laravel storage instance.
     *
     * @var \Illuminate\Contracts\Filesystem\Factory
     */
    protected $storage;

    /**
     * The explicit connection to use.
     *
     * @var string
     */
    protected $connectionName;

    /**
     * Creates a new Wingu Uploader instance.
     *
     * @param array                                    $config
     * @param \Illuminate\Filesystem\Filesystem        $filesystem
     * @param \Illuminate\Contracts\Filesystem\Factory $storage
     *
     * @return void
     */
    public function __construct(array $config, Filesystem $filesystem, Storage $storage)
    {
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->storage = $storage;
    }

    /**
     * Explicitly indicate which connection to use.
     *
     * @param string $connectionName
     */
    public function connection($connectionName)
    {
        if (!array_key_exists($connectionName, $this->config['configurations'])) {
            throw new UnexpectedValueException(sprintf("The specified connection '%s' has not been configured", $connectionName));
        }

        $this->connectionName = $connectionName;
    }

    /**
     * Uploads the file to the storage filesystem.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $resourceFile
     * @param array                                                      $customConnection
     *
     * @throws \Shoperti\Uploader\Exceptions\DisallowedFileException
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     *
     * @return \Shoperti\Uploader\UploadResult
     */
    public function upload($resourceFile, array $customConnection = [])
    {
        if (is_string($resourceFile)) {
            // this may throw a RemoteFileException
            $resourceFile = $this->getFileFromUrl($resourceFile);
        }

        // the connection has been specified, check if file has valid mime-type for it
        if ($this->connectionName) {
            if (!$this->checkConnectionForUploadedFile($resourceFile, $this->connectionName)) {
                throw new DisallowedFileException($resourceFile->getClientOriginalName(), $resourceFile->getMimeType());
            }
            $connection = $this->getConnectionByName($this->connectionName);

        // resolve the connection automatically
        } else {
            // may throw a DisallowedFileException, which should be caught on implementation
            $mimeType = $this->getMimeTypeFromAllowedFile($resourceFile);

            // may throw an UnexpectedValueException, which normally should not be caught on implementation
            $connection = $this->getConnectionForMimeType($mimeType);
        }

        // if upload method received some override settings, merge them to the config connection array
        if (!empty($customConnection)) {
            $connection = array_merge($connection, $customConnection);
        }

        $file = $this->processFile($resourceFile, $connection);

        $name = $this->getFileName($resourceFile, $connection);

        // Get relative file attributes
        $attributes = [];

        // File is an image, extract measures
        if ($file instanceof \Intervention\Image\Image) {
            /* @var \Intervention\Image\Image $file */
            $attributes['width'] = $file->getWidth();
            $attributes['height'] = $file->getHeight();
        }

        try {
            $wasMoved = $this->moveToStorage($file, $name, $connection);
            $e = null;
        } catch (Exception $e) {
            $wasMoved = false;
        }

        return new UploadResult($wasMoved, $resourceFile, $name, $connection, $attributes, $e);
    }

    /**
     * Gets a file stored in a remote location, accessible through http.
     *
     * @param string $url
     *
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile|null
     */
    public function getFileFromUrl($url)
    {
        $encodedUrl = str_replace(' ', '%20', $url);
        $tmpFileName = tempnam(sys_get_temp_dir(), 'wingufile-');

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
     * @param string $filename
     * @param string $location
     * @param string $path
     *
     * @throws \Shoperti\Uploader\Exceptions\FileNotFoundException
     *
     * @return bool true on success, false on failure.
     */
    public function delete($filename, $location, $path = null)
    {
        $filePath = (!empty($path) ? $path.'/' : '').$filename;

        try {
            return $this->storage->disk($location)->delete($filePath);
        } catch (LeagueFileNotFoundException $e) {
            throw new FileNotFoundException(
                $e->getPath(),
                $e->getCode(),
                $e
            );
        }
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

        if (in_array($fileMimeType, $this->setting('blocked_mimetypes', []))) {
            throw new DisallowedFileException(
                $uploadedFile->getClientOriginalName(),
                $fileMimeType,
                sprintf("File with not allowed mime-type '%s'", $fileMimeType)
            );
        }

        return $fileMimeType;
    }

    /**
     * Returns a connection by its name, replacing any closure by its returned value.
     *
     * @param string $connectionName
     *
     * @return array
     */
    protected function getConnectionByName($connectionName)
    {
        $connection = $this->config['connections'][$connectionName];

        return $this->prepareConnection($connection);
    }

    /**
     * Obtains the corresponding connection configuration depending on the file mime-type.
     *
     * @param string $fileMimeType
     *
     * @throws \UnexpectedValueException
     *
     * @return array
     */
    protected function getConnectionForMimeType($fileMimeType)
    {
        $selectedConnection = null;
        foreach ($this->setting('configurations', []) as $connection => $mimes) {
            foreach ($mimes as $connectionMimeType) {
                $correspondingConnection = null;

                // connection mime-type has ending wildcard, use 'starts with' comparison
                if (false !== strpos($connectionMimeType, '*')) {
                    $connectionMimeType = substr($connectionMimeType, 0, strpos($connectionMimeType, '*'));
                    // if $connectionMimeType is empty it was '*', use as default connection
                    if (empty($connectionMimeType) || 0 === strpos($fileMimeType, $connectionMimeType)) {
                        $correspondingConnection = $connection;
                    }
                } elseif ($fileMimeType == $connectionMimeType) {
                    $correspondingConnection = $connection;
                }

                if ($correspondingConnection) {
                    if (null === ($mimeConnection = $this->setting("connections.$connection"))) {
                        throw new UnexpectedValueException(sprintf(
                            "The file mime-type '%s' is configured to use the connection '%s' which does not exist",
                            $fileMimeType,
                            $connection
                        ));
                    }

                    $selectedConnection = $mimeConnection;
                }
            }
        }

        if (!$selectedConnection) {
            $selectedConnection = $this->setting('connections.'.$this->setting('default'));
        }

        return $this->prepareConnection($selectedConnection);
    }

    /**
     * Replaces any closure set as config value by its returned value.
     *
     * @param array $connection
     *
     * @return array
     */
    protected function prepareConnection($connection)
    {
        foreach ($connection as $key => $value) {
            if (is_object($value) && is_callable($value)) {
                $connection[$key] = $value();
            }
        }

        return $connection;
    }

    /**
     * Checks if the uploaded file (mime-type) can be processed by the specified connection.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param                                                     $connection
     *
     * @return bool
     */
    protected function checkConnectionForUploadedFile(UploadedFile $uploadedFile, $connection)
    {
        $allowedMimeTypes = $this->arrayGet($this->config['configurations'], $connection, []);
        $fileMimeType = $uploadedFile->getMimeType();

        if (empty($allowedMimeTypes)) {
            return false;
        }

        foreach ($allowedMimeTypes as $connectionMimeType) {
            // connection mime-type has ending wildcard, use 'starts with' comparison
            if (false !== strpos($connectionMimeType, '*')) {
                $connectionMimeType = substr($connectionMimeType, 0, strpos($connectionMimeType, '*'));
                // if $connectionMimeType is empty it was '*'
                if (empty($connectionMimeType) || 0 === strpos($fileMimeType, $connectionMimeType)) {
                    return true;
                }
            } elseif ($fileMimeType == $connectionMimeType) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resizes and the file in case of being an image with a processable extension.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param array                                               $connection
     *
     * @throws \Exception
     *
     * @return \Intervention\Image\Image|\Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected function processFile($uploadedFile, array $connection)
    {
        $isProcessableImg = in_array(
            $this->getFileExtension($uploadedFile, true),
            $this->arrayGet($connection, 'images_extensions', [])
        );

        if ($isProcessableImg) {
            return $this->resizeImage($uploadedFile, $connection);
        }

        return file_get_contents($uploadedFile->getRealPath());
    }

    /**
     * Resizes an image.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param array                                               $connection
     *
     * @throws \Exception
     *
     * @return \Intervention\Image\Image
     */
    protected function resizeImage($uploadedFile, array $connection)
    {
        $imageWidth = $this->arrayGet($connection, 'images_width');

        // temporally configure the memory limit
        $serverMemoryLimit = ini_get('memory_limit');
        $tempMemoryLimit = $this->arrayGet($connection, 'resize_memory_limit', '128M');
        ini_set('memory_limit', $tempMemoryLimit);

        // resize the image
        $image = Image::make($uploadedFile)
            ->resize($imageWidth, $imageWidth, function ($constraint) {
                /* @var \Intervention\Image\Constraint $constraint */
                $constraint->aspectRatio();
            })
            ->save();

        // reset original value
        ini_set('memory_limit', $serverMemoryLimit);

        return $image;
    }

    /**
     * Gets the file name from the uploaded file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param array                                               $connection
     *
     * @return string
     */
    protected function getFileName($uploadedFile, array $connection)
    {
        $renameStrategy = $this->arrayGet($connection, 'file_rename');

        switch ($renameStrategy) {
            case 'uniqid':
                $ext = $this->getFileExtension($uploadedFile);
                $prefix = $this->arrayGet($connection, 'filename_prefix', '');

                return str_replace('.', '-', uniqid($prefix, true)).'.'.$ext;

            case 'fix':
                $prefix = $this->arrayGet($connection, 'filename_prefix', '');
                $name = Str::ascii($prefix.pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
                $name = preg_replace('/_+/', '_', preg_replace("/[^a-z0-9_\-\.\,\+\*\(\)$']/i", "_", $name));
                $ext = $this->getFileExtension($uploadedFile);
                $location = $this->arrayGet($connection, 'location');
                $filesPath = $this->arrayGet($connection, 'files_directory');

                $filePath = (!empty($filesPath) ? "{$filesPath}/" : '').$name.'.'.$ext;
                if ($this->storage->disk($location)->exists($filePath)) {
                    $suffix = 0;
                    do {
                        $newName = $name.(++$suffix);
                        $filePath = (!empty($filesPath) ? "{$filesPath}/" : '').$newName.'.'.$ext;
                    } while ($this->storage->disk($location)->exists($filePath));
                    $name = $newName;
                }

                return "{$name}.{$ext}";
        }

        return $uploadedFile->getClientOriginalName();
    }

    /**
     * Gets the extension of a file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param bool                                                $guess
     *
     * @return string
     */
    protected function getFileExtension($uploadedFile, $guess = false)
    {
        return $guess
            ? $uploadedFile->guessExtension() ?: $uploadedFile->getClientOriginalExtension()
            : $uploadedFile->getClientOriginalExtension();
    }

    /**
     * Moves the uploaded file to the filesystem disk location.
     *
     * @param \Illuminate\Filesystem\Filesystem|\Intervention\Image\Image $file
     * @param string                                                      $filename
     * @param array                                                       $connection
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function moveToStorage($file, $filename, array $connection)
    {
        $location = $this->arrayGet($connection, 'location');
        $filePath = $this->arrayGet($connection, 'files_directory');
        $filePath .= (!empty($filePath) ? '/' : '').$filename;

        // put() may throw an \InvalidArgumentException
        return $this->storage->disk($location)->put($filePath, (string) $file);
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
     * Gets a config setting.
     *
     * @param string|array $key
     * @param mixed|null   $default
     *
     * @return mixed|null
     */
    protected function setting($key, $default = null)
    {
        return array_get($this->config, $key, $default);
    }

    /**
     * Gets the value of the specified key in the received array or returns the default if key does not exist.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function arrayGet($array, $key, $default = null)
    {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }
}
