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
      * Do not upload any file with the following mime-types:
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

     'configurations' => [
        'images' => [
            'disk'                => env('UPLOADER_IMAGES_STORAGE', 'local'),

            'subpath'             => 'images',

            'file_rename'         => 'uniqid',

            'allowed_extensions'  => ['jpeg', 'jpg', 'png', 'gif'],

            'max_width'           => 1280,

            'resize_memory_limit' => '128M',
        ],
        'files' => [
            'disk'                => 's3',

            'file_rename'         => 'uniqid',

            'subpath'             => 'files',

            'allowed_extensions'  => [],
        ]
      ],



     /*
      * This allows to define which connection config to use depending on the file mime-type.
      */
     'configurations' => [
         'images' => [
             'image/*',
         ],
         // if this is defined will work as default for the uploader, overriding the 'default' setting
         // 'default' => [
         //     '*',
         // ],
     ],

     /*
      * Default connection for Laravel-Manager
      */
     'default' => 'default',

     /*
      * Do not upload any file with the following mime-types:
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
      * Connection configurations.
      *
      * Note: Each connection setting can be also set as a callable function that returns a value of
      * the expected type.
      */
     'connections' => [
         /*
          * The default configuration.
          */
         'default' => [
             /*
              * Default filesystem disk driver or location for the original uploaded files.
              *
              * Supported values: 'local', 's3', 'rackspace'
              *
              * Before using the S3 or Rackspace drivers, you will need to install the appropriate package via Composer:
              *     laravel 5.1 | Lumen
              *         Amazon S3: league/flysystem-aws-s3-v3 ~1.0
              *         Rackspace: league/flysystem-rackspace ~1.0
              *
              *     laravel 5.0
              *         Amazon S3: league/flysystem-aws-s3-v2 ~1.0
              *         Rackspace: league/flysystem-rackspace ~1.0
              */
             'location' => 'local',

             /*
              * Default filesystem disk driver or location for the resized images cache
              *
              * Supported values: 'local', 's3', 'rackspace'
              *
              * Before using the S3 or Rackspace drivers, you will need to install the appropriate package via Composer:
              *     laravel 5.1 | Lumen
              *         Amazon S3: league/flysystem-aws-s3-v3 ~1.0
              *         Rackspace: league/flysystem-rackspace ~1.0
              *
              *     laravel 5.0
              *         Amazon S3: league/flysystem-aws-s3-v2 ~1.0
              *         Rackspace: league/flysystem-rackspace ~1.0
              */
             'cache_location' => 'local',

             /*
              * Filename renaming strategy.
              *
              * Supported values:
              *     'none'|null : Files wont be renamed this will rewrite files with the same name
              *     'uniqid'    : Files will be renamed with using uniqid() (http://php.net/manual/en/function.uniqid.php)
              *     'fix'       : Files will be renamed with an indexed suffix if there's already a file with the same name
              */
             'file_rename' => 'uniqid',

             /*
              * Prefix to add to the filename, set as null or empty string for not using any
              */
             'filename_prefix' => 'file_',

             'files_directory' => 'files',
             'cache_directory' => 'files/.cache',

             /*
              * If 'location' is not local, this will be used to construct the uploaded file URL
              */
             'remote_url'          => 'https://<bucket>.s3.amazonaws.com',

             /*
              * Base URL route for serving files.
              *
              * This value MUST be the same than the route endpoint for retrieving the uploaded files
              */
             'url_subpath' => 'files',

             /*
              * File extensions to resize on upload
              */
             'images_extensions' => [
                 'jpeg',
                 'jpg',
                 'png',
                 'gif',
             ],

             /*
              * Default width for the original uploaded file,
              * Null indicates that no resize will be made
              *
              * Supported values: null|int
              */
             'image_width' => 1024,

             /*
              * PHP memory limit override for the upload resizing
              */
             'resize_memory_limit' => '128M',
         ],

         /*
          * Images configuration example.
          */
         'images' => [
             'location'            => 'local',
             'cache_location'      => 'local',
             'file_rename'         => 'uniqid',
             'filename_prefix'     => 'img_',
             'files_directory'     => 'images',
             'cache_directory'     => 'images/.cache',
             'remote_url'          => 'https://<bucket>.s3.amazonaws.com',
             'url_subpath'         => 'images',
             'images_extensions'   => ['jpeg', 'jpg', 'png', 'gif'],
             'images_width'        => 1280,
             'resize_memory_limit' => '128M',
         ],
     ],
 ];
