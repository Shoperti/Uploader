<?php

namespace Shoperti\Uploader;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Shoperti\Uploader\Exceptions\DisallowedFileException;
use Shoperti\Uploader\Exceptions\InvalidConfigurationException;

/**
 * Class ConfigurationManager.
 *
 * Class for resolving and handling configurations.
 *
 * @author Arturo Rodríguez <arturo@shoperti.com>
 */
class ConfigurationManager
{
    /**
     * The package configuration.
     *
     * @var array
     */
    protected $packageConfiguration;

    /**
     * The resolved configuration for the currently uploaded file.
     *
     * @var array
     */
    protected $configuration;

    /**
     * Creates a new ConfigurationManager instance.
     *
     * @param array $packageConfiguration
     *
     * @return void
     */
    public function __construct($packageConfiguration)
    {
        $this->packageConfiguration = $packageConfiguration;
    }

    /**
     * Resolves the configuration to use according to the uploaded file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     *
     * @throws \Shoperti\Uploader\Exceptions\DisallowedFileException
     * @throws \Shoperti\Uploader\Exceptions\InvalidConfigurationException
     *
     * @return void
     */
    public function resolve($uploadedFile)
    {
        $fileMimeType = $this->getMimeTypeFromAllowedFile($uploadedFile);

        $this->configuration = $this->getConnectionForMimeType($fileMimeType);
    }

    /**
     * Gets a configuration setting.
     *
     * @param string|array $key
     * @param mixed|null   $default
     *
     * @return mixed|null
     */
    public function setting($key, $default = null)
    {
        return Arr::get($this->configuration, $key, $default);
    }

    /**
     * Gets and fixes the configured subpath setting.
     *
     * @return string
     */
    public function subpathSetting()
    {
        $subpath = $this->setting('subpath', '');

        return !empty($subpath)
            ? (!Str::endsWith($subpath, '/') ? "{$subpath}/" : $subpath)
            : '';
    }

    /**
     * Gets the full resolved configuration settings.
     *
     * @return array
     */
    public function getResolvedConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Gets the file mime-type if it's part of the configured allowed mime-types.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     *
     * @throws \Shoperti\Uploader\Exceptions\DisallowedFileException
     *
     * @return string
     */
    protected function getMimeTypeFromAllowedFile($uploadedFile)
    {
        $fileMimeType = $uploadedFile->getMimeType();

        if (in_array($fileMimeType, $this->packageSetting('blocked_mimetypes', []))) {
            throw new DisallowedFileException(
                $uploadedFile->getClientOriginalName(),
                $fileMimeType,
                sprintf("File with not allowed mime-type '%s'", $fileMimeType)
            );
        }

        return $fileMimeType;
    }

    /**
     * Obtains the corresponding connection configuration depending on the file mime-type.
     *
     * @param string $fileMimeType
     *
     * @throws \Shoperti\Uploader\Exceptions\InvalidConfigurationException
     *
     * @return array
     */
    protected function getConnectionForMimeType($fileMimeType)
    {
        foreach ($this->packageSetting('mime_resolvers', []) as $configuration => $configurationMimes) {
            foreach ($configurationMimes as $configurationMime) {
                $foundConfiguration = null;

                if ($fileMimeType === $configurationMime) {
                    $foundConfiguration = $configuration;
                }
                // connection mime-type has ending wildcard, use 'starts with' comparison
                elseif (substr_count($configurationMime, '*') === 1) {
                    $configurationMime = '#'.str_replace('*', '.+', $configurationMime).'#';
                    if (preg_match($configurationMime, $fileMimeType)) {
                        $foundConfiguration = $configuration;
                    }
                }

                if ($foundConfiguration) {
                    if ($configuration = $this->packageSetting("configurations.$configuration")) {
                        return $configuration;
                    } else {
                        break 2;
                    }
                }
            }
        }

        throw new InvalidConfigurationException(sprintf("No configuration found for '%s'", $fileMimeType));
    }

    /**
     * Gets a setting from the package settings.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function packageSetting($key, $default = null)
    {
        return Arr::get($this->packageConfiguration, $key, $default);
    }
}
