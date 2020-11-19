# Shoperti Uploader

[![CircleCI](https://circleci.com/gh/Shoperti/Uploader.svg?style=svg&circle-token=4a365e65b98c185c1778469c6a618e0543022169)](https://circleci.com/gh/Shoperti/Uploader)

A file uploader for Laravel with support for preprocessing images (resize and auto orientation) using League/Glide.

Note: For auto orientation you must have a PHP installation that supports reading exif.

## Installation

Add to composer

```json
    "require": {
        "shoperti/uploader": "~5.6"
    },
```


Register the package in one of your providers

```php
    public function register()
    {
        $this->app->register(\Shoperti\Uploader\UploaderServiceProvider::class);
    }
```

## Configuration

You can configure it through the .env file using these settings:

```
UPLOADER_FILES_DISK=s3
UPLOADER_FILES_SUBPATH=files
UPLOADER_FILES_FILE_NAMING=fix

UPLOADER_IMAGES_DISK=s3
UPLOADER_IMAGES_SUBPATH=images
UPLOADER_IMAGES_FILE_NAMING=fix
UPLOADER_IMAGES_RESIZE_MAX_WIDTH=1280
UPLOADER_IMAGES_RESIZE_MEMORY_LIMIT=128M
```

* _Please refer to the [config file](config/uploader.php) for more details._


## Use

```php
<?php

namespace App\Http\Controllers;

// Import the Shoperti Uploader Manager in your controller
use Shoperti\Uploader\Contracts\UploaderManager;
use Shoperti\Uploader\Exceptions\DisallowedFileException;
use Shoperti\Uploader\Exceptions\InvalidFileException;
use Shoperti\Uploader\Exceptions\RemoteFileException;

// To upload or delete a file, just inject the manager either in the constructor
// or in the action method

class Controller extends BaseController
{
   /**
    * Uploads a file.
    *
    * @param \Shoperti\Uploader\Contracts\UploaderManager $uploaderManager
    */
    public function upload(UploaderManager $uploaderManager)
    {
        try {
            /** @var \Shoperti\Uploader\UploadResult uploadResult */
            $uploadResult = $uploaderManager

                // generate an Uploader through the manager
                // using the uploaded file or the file URL as argument
                ->make(request()->file('file') ?: request()->input('file'))

                // then call the upload() method with the location path as argument
                ->upload($path = 'my_files', $disk = null);

        } catch (DisallowedFileException $dfe) {

            // If the uploaded file has a disallowed mime-type

        } catch (InvalidFileException $ife) {

            // If the uploaded file is invalid

        } catch (RemoteFileException $rfe) {

            // If the file input was a file-url string which cannot be fetched

        }
    }

   /**
    * Deletes a file.
    *
    * @param \Shoperti\Uploader\Contracts\UploaderManager $uploaderManager
    */
    public function delete(UploaderManager $uploaderManager)
    {
        $uploaderManager
            ->delete($disk = 's3', $filepath = \Request::input('file'))
    }
}
```

* _Please refer to the [`UploadResult` implementation](src/UploadResult.php) for more details._
