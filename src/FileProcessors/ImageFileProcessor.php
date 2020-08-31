<?php

namespace Shoperti\Uploader\FileProcessors;

use Illuminate\Support\Arr;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Exception\NotSupportedException;
use Intervention\Image\ImageManagerStatic as ImageManager;
use Shoperti\Uploader\Contracts\FileProcessor;
use Shoperti\Uploader\Exceptions\InvalidFileException;

/**
 * This is the image processor class.
 *
 * @author Arturo Rodríguez <arturo@shoperti.com>
 * @author Joseph Cohen <joe@shoperti.com>
 */
class ImageFileProcessor extends BaseFileProcessor implements FileProcessor
{
    /**
     * Processes a file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param array                                               $config
     *
     * @throws \Shoperti\Uploader\Exceptions\InvalidFileException
     *
     * @return string
     */
    public function process($file, array $config = [])
    {
        $imageWidth = Arr::get($config, 'image_resize_max_width');

        // temporally configure the memory limit
        $originalMemoryLimit = ini_get('memory_limit');

        $configMemoryLimit = Arr::get($config, 'image_resize_memory_limit', '192M');

        ini_set('memory_limit', $configMemoryLimit);

        try {
            $image = ImageManager::make($file);
        } catch (NotReadableException $e) {
            throw new InvalidFileException($file->getClientOriginalName(), $file->getClientMimeType(), $e);
        }

        $image->resize($imageWidth, $imageWidth, function ($constraint) {
            /* @var \Intervention\Image\Constraint $constraint */
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        if (function_exists('exif_read_data')) {
            $image->orientate();
        }

        try {
            $image->save();
        } catch (NotSupportedException $e) {
            // e.g. Encoding format (jfif) is not supported
            throw new InvalidFileException($file->getClientOriginalName(), $file->getMimeType(), $e);
        }

        // reset original value
        ini_set('memory_limit', $originalMemoryLimit);

        return (string) $image;
    }
}
