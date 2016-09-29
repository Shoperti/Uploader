<?php

namespace Shoperti\Uploader\Contracts;

/**
 * This is the uploader inteface class.
 *
 * @author Arturo Rodrígez <arturo@shoperti.com>
 */
interface Factory
{
    public function make($uploadedFile);

    /**
     * Gets a file stored in a remote location, accessible through HTTP.
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
