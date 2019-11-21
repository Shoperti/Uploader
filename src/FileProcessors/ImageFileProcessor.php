<?php

namespace Shoperti\Uploader\FileProcessors;

use Illuminate\Support\Arr;
use Intervention\Image\ImageManagerStatic as ImageManager;
use Shoperti\Uploader\Contracts\FileProcessor;

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
     * @throws \Intervention\Image\Exception\NotReadableException
     *
     * @return string
     */
    public function process($file, array $config = [])
    {
        $imageWidth = Arr::get($config, 'image_resize_max_width');

        // temporally configure the memory limit
        $originalMemoryLimit = ini_get('memory_limit');

        $configMemoryLimit = Arr::get($config, 'image_resize_memory_limit', '128M');

        ini_set('memory_limit', $configMemoryLimit);

        // process the image, may throw NotReadableException
        $image = ImageManager::make($file);

        $image->resize($imageWidth, $imageWidth, function ($constraint) {
            /* @var \Intervention\Image\Constraint $constraint */
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        if (function_exists('exif_read_data')) {
            $image->orientate();
        }

        $image->save();

        // reset original value
        ini_set('memory_limit', $originalMemoryLimit);

        return $image;
    }
}
