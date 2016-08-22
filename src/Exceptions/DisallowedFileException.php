<?php

namespace Shoperti\Uploader\Exceptions;

use Exception;

/**
 * This is the disallowed file exception class.
 *
 * @author Arturo Rodrígez <arturo@shoperti.com>
 */
class DisallowedFileException extends Exception
{
    /**
     * The invalid file name.
     *
     * @var string
     */
    private $name;

    /**
     * The invalid file mime-type.
     *
     * @var string
     */
    private $mimeType;

    /**
     * Creates a new DisallowedFileException object.
     *
     * @param string $name
     * @param string $mimeType
     * @param string $message
     *
     * @return void
     */
    public function __construct($name, $mimeType, $message = null)
    {
        $this->name = $name;
        $this->mimeType = $mimeType;

        parent::__construct($message);
    }

    /**
     * Gets the invalid file name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the invalid file mime-type.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }
}
