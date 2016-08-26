# Shoperti Uploader

[![CircleCI](https://circleci.com/gh/Shoperti/Uploader.svg?style=svg&circle-token=4a365e65b98c185c1778469c6a618e0543022169)](https://circleci.com/gh/Shoperti/Uploader)

A file uploader for Laravel with support for preprocessing images (resize and auto orientation) using League/Glide.

Note: For auto orientation you must have a PHP installation that supports reading exif.

## Installation

Add to composer

```json
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Shoperti/Uploader"
        }
    ],
    ...

    "require": {
        "shoperti/uploader": "dev-master"
    },
```


and register the package in one of your providers

```php
    public function register()
    {
        $this->app->register(\Shoperti\Uploader\UploaderServiceProvider::class);
    }
```

## Configuration

Out of the box you can configure it through the .env file using these settings:

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

Import the Shoperti Uploader in your controller

```php
use Shoperti\Uploader\Contracts\Uploader;
```

inject the object either in the constructor or in the action method

```php
public function persist(..., Uploader $uploader)
```

After getting the file from the request input, call the `upload()` method
to process the file with the uploader.

```php
try {

    // $file may be an UploadedFile object sent through a file input or
    // an URL string sent using a text input

    /** @var \Shoperti\Uploader\UploadResult $uploadResult */
    $uploadResult = $uploader->upload($file);

} catch (\Shoperti\Uploader\Exceptions\DisallowedFileException $dfe) {

    // If the uploaded file has a disallowed mime-type

} catch (\Shoperti\Uploader\Exceptions\RemoteFileException $rfe) {

    // If the file input was a file-url string which cannot be fetched

}
```

For deleting an uploaded file you can use the `delete()` method

```php
$uploader->delete(\Request::input('file'), 's3')
```

The `upload()` method will return a `\Shoperti\Upload\UploadResult` instance with the upload result.

* _Please refer to the [`UploadResult` implementation](src/UploadResult.php) for more details._


### TODO
[ ] Test functionality
