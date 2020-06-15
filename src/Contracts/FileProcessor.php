<?php

namespace Shoperti\Uploader\Contracts;

/**
 * This is the processor interface class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
interface FileProcessor
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
    public function process($file, array $config = []);
}
