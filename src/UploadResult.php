<?php

namespace Shoperti\Uploader;

/**
 * The UploadResult class.
 *
 * @author Arturo Rodríguez <arturo@shoperti.com>
 */
class UploadResult
{
    /**
     * Indicates if the file was successfully stored.
     *
     * @var bool
     */
    protected $isUploaded;

    /**
     * The sent file.
     *
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected $uploadedFile;

    /**
     * The uploaded file url.
     *
     * @var string
     */
    protected $url;

    /**
     * The file name.
     *
     * @var string
     */
    protected $name;

    /**
     * The original file name.
     *
     * @var string
     */
    protected $originalName;

    /**
     * The file mime-type.
     *
     * @var string
     */
    protected $mimeType;

    /**
     * The file size.
     *
     * @var int
     */
    protected $size;

    /**
     * The storage disk.
     *
     * @var string
     */
    protected $disk;

    /**
     * The file location.
     *
     * @var string
     */
    protected $subpath;

    /**
     * File-relative attributes.
     *
     * @var array
     */
    protected $attributes;

    /**
     * May contain an upload exception.
     *
     * @var \Exception
     */
    protected $exception;

    /**
     * Creates a new UploadResult object.
     *
     * @param bool                                                $isUploaded
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param string|null                                         $url
     * @param array                                               $configuration
     * @param string                                              $name
     * @param array                                               $attributes
     * @param \Exception|null                                     $exception
     */
    public function __construct(
        $isUploaded,
        $uploadedFile,
        $url,
        $configuration,
        $name,
        $attributes,
        $exception = null
    ) {
        $this->isUploaded = $isUploaded;
        $this->uploadedFile = $uploadedFile;
        $this->url = $url;
        $this->name = $name;
        $this->originalName = $uploadedFile->getClientOriginalName();
        $this->mimeType = $uploadedFile->getMimeType();
        $this->size = $uploadedFile->getSize();
        $this->disk = $configuration['disk'];
        $this->subpath = $configuration['subpath'];
        $this->attributes = $attributes;
        $this->exception = $exception;
    }

    /**
     * Gets the indication of if the file was successfully stored.
     *
     * @return bool
     */
    public function getIsUploaded()
    {
        return $this->isUploaded;
    }

    /**
     * Gets the uploaded file.
     *
     * @return mixed|\Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * Gets the uploaded file url.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Gets the assigned file name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the original name of the uploaded file.
     *
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * Gets the uploaded file mime-type.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Gets the uploaded file size.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Gets the storage disk.
     *
     * @return string
     */
    public function getDisk()
    {
        return $this->disk;
    }

    /**
     * Gets the file location subpath.
     *
     * @return string
     */
    public function getSubpath()
    {
        return $this->subpath;
    }

    /**
     * Gets the file-relative attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Gets any thrown exception while trying to upload the file.
     *
     * @return null|\Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Gets the file width if file has that property.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->getAttribute('width');
    }

    /**
     * Gets the file width if file has that property.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->getAttribute('height');
    }

    /**
     * Gets the value of a file-relative attribute if it's defined.
     *
     * @param string     $attribute
     * @param mixed|null $default
     *
     * @return int|string|mixed
     */
    public function getAttribute($attribute, $default = null)
    {
        return isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : $default;
    }
}
