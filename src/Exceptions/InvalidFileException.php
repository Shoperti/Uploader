<?php

namespace Shoperti\Uploader\Exceptions;

/**
 * This is the invalid file exception class.
 *
 * @author Arturo Rodríguez <arturo@shoperti.com>
 */
class InvalidFileException extends FileException
{
    /**
     * Creates a new instance.
     *
     * @param string          $name
     * @param string|null     $mimeType
     * @param \Exception|null $previous
     *
     * @return void
     */
    public function __construct($name, $mimeType, $previous = null)
    {
        $mimeType = $mimeType ?: 'unknown';

        $msg = "The file $name with mime-type $mimeType is not valid";

        if ($previous && $previous->getMessage()) {
            $msg .= ": {$previous->getMessage()}";
        }

        parent::__construct($name, $mimeType, $msg, $previous);
    }
}
