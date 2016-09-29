<?php

namespace Shoperti\Uploader\Processors;

use Shoperti\Uploader\HandlesFilesTrait;

class FileProcessor extends Processor implements ProcessorInterface
{
    use HandlesFilesTrait;

    public function process($file, array $config = [])
    {
        return file_get_contents($file->getRealPath());
    }
}
