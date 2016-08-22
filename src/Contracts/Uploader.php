<?php

namespace Shoperti\Uploader\Contracts;

/**
 * This is the uploader inteface class.
 *
 * @author Arturo Rodrígez <arturo@shoperti.com>
 */
interface Uploader
{
    /**
     * Explicitly indicate which connection to use.
     *
     * @param string $connectionName
     */
    public function connection($connectionName);

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
    public function upload($resourceFile, array $customConnection = []);

    /**
     * Gets a file stored in a remote location, accessible through http.
     *
     * @param string $url
     *
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile|null
     */
    public function getFileFromUrl($url);

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
    public function delete($filename, $location, $path);
}
