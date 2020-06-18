<?php

namespace Shoperti\Uploader\Exceptions;

use Exception;

/**
 * This is the file exception class.
 *
 * @author Arturo Rodríguez <arturo@shoperti.com>
 */
class FileException extends Exception
{
    /**
     * The file name.
     *
     * @var string
     */
    private $name;

    /**
     * The file mime-type.
     *
     * @var string
     */
    private $mimeType;

    /**
     * Creates a new instance.
     *
     * @param string          $name
     * @param string|null     $mimeType
     * @param string|null     $message
     * @param \Exception|null $previous
     *
     * @return void
     */
    public function __construct($name, $mimeType, $message = null, $previous = null)
    {
        $this->name = $name;
        $this->mimeType = $mimeType;

        parent::__construct($message, 0, $previous);
    }

    /**
     * Gets the file name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the file mime-type.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }
}
