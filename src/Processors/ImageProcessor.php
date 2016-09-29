<?php

namespace Shoperti\Uploader\Processors;

use Illuminate\Support\Arr;
use Intervention\Image\Image;
use Intervention\Image\ImageManagerStatic as ImageManager;

class ImageProcessor extends Processor implements ProcessorInterface
{
    public function process($file, array $config = [])
    {
        $imageWidth = Arr::get($config, 'image_resize_max_width');

        // temporally configure the memory limit
        $originalMemoryLimit = ini_get('memory_limit');

        $configMemoryLimit = Arr::get($config, 'image_resize_memory_limit', '128M');

        ini_set('memory_limit', $configMemoryLimit);

        // process the image
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
