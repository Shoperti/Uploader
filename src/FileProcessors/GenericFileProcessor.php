<?php

namespace Shoperti\Uploader\FileProcessors;

use Shoperti\Uploader\Contracts\FileProcessor;
use Shoperti\Uploader\Exceptions\InvalidFileException;

/**
 * This is the file processor class.
 *
 * @author Joseph Cohen <joe@shoperti.com>
 */
class GenericFileProcessor extends BaseFileProcessor implements FileProcessor
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
        $content = file_get_contents($file->getRealPath());

        if ($content === false) {
            throw new InvalidFileException($file->getClientOriginalName(), $file->getClientMimeType());
        }

        return $content;
    }
}
