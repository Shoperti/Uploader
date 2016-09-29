<?php

namespace Shoperti\Uploader\Processors;

interface ProcessorInterface
{
    public function process($file, array $config = []);
}
