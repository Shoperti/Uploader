<?php

namespace Shoperti\Uploader\Exceptions;

use Exception;

/**
 * This is the remove file exception class.
 *
 * @author Arturo Rodrígez <arturo@shoperti.com>
 */
class RemoteFileException extends Exception
{
    /**
     * The file url.
     *
     * @var string
     */
    private $url;

    /**
     * Creates a new remote file exception instance.
     *
     * @param string $url
     * @param string $message
     *
     * @return void
     */
    public function __construct($url, $message = null)
    {
        $this->url = $url;

        parent::__construct($message);
    }

    /**
     * Gets the invalid file url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
