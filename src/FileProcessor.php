<?php

namespace Shoperti\Uploader;

use Intervention\Image\Image;
use Intervention\Image\ImageManagerStatic as ImageManager;

/**
 * Class FileProcessor.
 *
 * The class for processing the uploaded files.
 *
 * @author Arturo Rodríguez <arturo@shoperti.com>
 */
class FileProcessor
{
    use HandlesFilesTrait;

    /**
     * Processes the uploaded file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param \Shoperti\Uploader\ConfigurationManager             $configurationManager
     *
     * @throws \Exception
     *
     * @return array [
     *               . \Intervention\Image\Image|\Symfony\Component\HttpFoundation\File\UploadedFile,
     *               . [attributes]
     *               ]
     */
    public function process($uploadedFile, ConfigurationManager $configurationManager)
    {
        $isProcessableImg = in_array(
            $this->getFileExtension($uploadedFile, true),
            ['jpeg', 'jpg', 'png', 'gif']
        );

        $file = $isProcessableImg
            ? $this->processImage($uploadedFile, $configurationManager)
            : file_get_contents($uploadedFile->getRealPath());

        $attributes = [];

        // File is an image, extract measures
        if ($file instanceof Image) {
            /* @var Image $file */
            $attributes['width'] = $file->getWidth();
            $attributes['height'] = $file->getHeight();
        }

        return [$file, $attributes];
    }

    /**
     * Processes an image.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param \Shoperti\Uploader\ConfigurationManager             $configurationManager
     *
     * @throws \Exception
     *
     * @return \Intervention\Image\Image
     */
    protected function processImage($uploadedFile, ConfigurationManager $configurationManager)
    {
        $imageWidth = $configurationManager->setting('image_resize_max_width');

        // temporally configure the memory limit
        $serverMemoryLimit = ini_get('memory_limit');
        $tempMemoryLimit = $configurationManager->setting('image_resize_memory_limit', '128M');
        ini_set('memory_limit', $tempMemoryLimit);

        // resize the image
        $image = ImageManager::make($uploadedFile);

        $image->resize($imageWidth, $imageWidth, function ($constraint) {
            /* @var \Intervention\Image\Constraint $constraint */
            $constraint->aspectRatio();
        });

        if (function_exists('exif_read_data')) {
            $image->orientate();
        }

        $image->save();

        // reset original value
        ini_set('memory_limit', $serverMemoryLimit);

        return $image;
    }
}
