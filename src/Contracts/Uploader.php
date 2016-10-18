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
     * @param string|null $path
     * @param string|null $disk
     *
     * @throws \Shoperti\Uploader\Exceptions\DisallowedFileException
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     *
     * @return \Shoperti\Uploader\UploadResult
     */
    public function upload($path = null, $disk = null);

    /*
     * Uploads a file to a filesystem disk with a name.
     *
     * @param string      $name
     * @param string|null $path
     * @param string|null $disk
     *
     * @throws \Shoperti\Uploader\Exceptions\DisallowedFileException
     * @throws \Shoperti\Uploader\Exceptions\RemoteFileException
     *
     * @return \Shoperti\Uploader\UploadResult
     */
    public function uploadAs($name, $path = null, $disk = null);
}
