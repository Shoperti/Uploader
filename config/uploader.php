<?php

/*
 * This file is part of Shoperti Uploader.
 *
 * (c) Joseph Cohen <joe@shoperti.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /*
     * Do not upload any file with the following mime-types. This is checked before resolving the configuration.
     *
     * If an uploaded file mime-type is found here a \Shoperti\Uploader\Exceptions\DisallowedFileException
     * will be thrown.
     */
    'blocked_mimetypes' => [
        'application/dos-exe',
        'application/exe',
        'application/msdos-windows',
        'application/octet-stream',
        'application/x-dosexec',
        'application/x-exe',
        'application/x-msdos-program',
        'application/x-msdownload',
        'application/x-winexe',
        'vms/exe',
    ],

    /*
     * GLOBAL SETTINGS
     *
     * disk: The disk to use for storing the file: 'local' | 'public' | 'ftp' | 's3' | 'rackspace'
     *       The FTP adn Rackspace disks don't come configured, please refer to the laravel documentation to use them
     *       Before using the S3 or Rackspace drivers, you will need to install the appropriate package via Composer
     *       Amazon S3: league/flysystem-aws-s3-vX
     *       Rackspace: league/flysystem-rackspace
     *
     * subpath: The directory to use in the specified disk
     *       Before using the S3 or Rackspace drivers, you will need to install the appropriate package via Composer
     *
     * file_naming: The naming strategy for renaming the uploading files
     *     'none'       : Files wont be renamed, this will rewrite any file with the same name (not recommended)
     *     'fix'        : Files names will be fixed placing problematic characters for download
     *     'fix_unique' : Same than fix but add a numeric suffix if a file with the same name already exists
     *     'uniqid'     : Files will be renamed with using uniqid() (http://php.net/manual/en/function.uniqid.php)
     *
     *
     * IMAGES ONLY
     *
     * image_resize_max_width: The maximum with for resizeable images
     *     INT  : The width size in pixels
     *     null : No resize will be preformed
     *
     * image_resize_memory_limit: The maximum amount of memory to use when processing images
     */
    'configurations' => [
        'images' => [
            'processor'                 => 'image',
            'disk'                      => env('UPLOADER_IMAGES_DISK', 's3'),
            'subpath'                   => env('UPLOADER_IMAGES_SUBPATH', 'images'),
            'file_naming'               => env('UPLOADER_IMAGES_FILE_NAMING', 'fix'),
            'image_resize_max_width'    => env('UPLOADER_IMAGES_RESIZE_MAX_WIDTH', 1280),
            'image_resize_memory_limit' => env('UPLOADER_IMAGES_RESIZE_MEMORY_LIMIT', '128M'),
        ],
        'files' => [
            'processor'   => 'file',
            'disk'        => env('UPLOADER_FILES_DISK', 's3'),
            'subpath'     => env('UPLOADER_FILES_SUBPATH', 'files'),
            'file_naming' => env('UPLOADER_FILES_FILE_NAMING', 'fix'),
        ],
      ],

    /*
     * This define which connection configuration will be used depending on the file mime-type.
     *
     * Use '*' as wildcard.
     */
    'mime_resolvers' => [
        'images' => [
            'image/*',
        ],
        'files' => [
            '*',
        ],
    ],
 ];
