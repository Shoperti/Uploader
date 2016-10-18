<?php

namespace Shoperti\Uploader\FileProcessors;

use Shoperti\Uploader\Contracts\FileProcessor as FileProcessorInterface;

/**
 * This is the file processor class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
class GenericFileProcessor extends BaseFileProcessor implements FileProcessorInterface
{
    /**
     * Processes a file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param array                                               $config
     *
     * @return string
     */
    public function process($file, array $config = [])
    {
        return file_get_contents($file->getRealPath());
    }
}
