<?php

namespace Shoperti\Uploader\Contracts;

/**
 * This is the uploader manager interface class.
 *
 * @author Arturo Rodrígez <arturo@shoperti.com>
 */
interface UploaderManager
{
    /**
     * Makes a new uploader instance.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param string|null                                         $connection
     *
     * @return \Shoperti\Uploader\Uploader
     */
    public function make($uploadedFile, $connection);

    /**
     * Gets a file to process.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getFile($file);

    /**
     * Gets a file stored in a remote location accessible through HTTP.
     *
     * @param string $url
     *
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getFileFromUrl($url);

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
    public function delete($disk, $filePath);
}
