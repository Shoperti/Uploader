<?php

namespace Shoperti\Uploader\Processors;

/**
 * This is the processor interface class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
interface ProcessorInterface
{
    /**
     * Process a file.
     *
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param  array                                               $config
     *
     * @return sting
     */
    public function process($file, array $config = []);
}
