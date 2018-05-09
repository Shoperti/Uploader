<?php

namespace Shoperti\Uploader\Contracts;

/**
 * This is the uploader interface class.
 *
 * @author Arturo Rodrígez <arturo@shoperti.com>
 * @author Joseph Cohen <joe@shoperti.com>
 */
interface Uploader
{
    /**
     * Uploads a file to a filesystem disk.
     *
     * @param string      $path
     * @param string|null $disk
     * @param mixed       $options
     *
     * @throws \Shoperti\Uploader\Exceptions\DisallowedFileException
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     *
     * @return \Shoperti\Uploader\UploadResult
     */
    public function upload($path, $disk = null, $options = []);

    /*
     * Uploads a file to a filesystem disk with a name.
     *
     * @param string      $path
     * @param string      $name
     * @param string|null $disk
     * @param mixed       $options
     *
     * @throws \Shoperti\Uploader\Exceptions\DisallowedFileException
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     *
     * @return \Shoperti\Uploader\UploadResult
     */
    public function uploadAs($path, $name, $disk = null, $options = []);
}
