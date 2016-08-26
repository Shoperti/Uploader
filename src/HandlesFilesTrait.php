<?php

namespace Shoperti\Uploader;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Trait HandlesFilesTrait.
 *
 * Some helper methods related to uploaded files.
 *
 * @author Arturo Rodríguez <arturo@shoperti.com>
 */
trait HandlesFilesTrait
{
    /**
     * Gets the name of a file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     *
     * @return string
     */
    protected function getFileName($uploadedFile)
    {
        return pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
    }

    /**
     * Gets the extension of a file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param bool                                                $guess
     *
     * @return string
     */
    protected function getFileExtension(UploadedFile $uploadedFile, $guess = false)
    {
        return $guess
            ? $uploadedFile->guessExtension() ?: $uploadedFile->getClientOriginalExtension()
            : $uploadedFile->getClientOriginalExtension();
    }
}
