<?php

namespace Shoperti\Uploader\Processors;

/**
 * This is the file processor class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
class FileProcessor extends Processor implements ProcessorInterface
{
    /**
     * Process a file.
     *
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param  array                                               $config
     *
     * @return sting
     */
    public function process($file, array $config = [])
    {
        return file_get_contents($file->getRealPath());
    }
}
