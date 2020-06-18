<?php

namespace Shoperti\Uploader\Exceptions;

/**
 * This is the disallowed file exception class.
 *
 * @author Arturo Rodríguez <arturo@shoperti.com>
 */
class DisallowedFileException extends FileException
{
    /**
     * Creates a new instance.
     *
     * @param string      $name
     * @param string|null $mimeType
     *
     * @return void
     */
    public function __construct($name, $mimeType)
    {
        $msg = "The file $name with mime-type $mimeType is not allowed";

        parent::__construct($name, $mimeType, $msg);
    }
}
