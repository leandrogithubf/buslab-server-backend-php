<?php

namespace App\Topnode\FileBundle\Utils;

use App\Topnode\BaseBundle\Utils\Multimedia\FileHandler;

class FileHandlerDecisor
{
    private $localFileHandler;

    public function __construct(FileHandler $localFileHandler)
    {
        $this->localFileHandler = $localFileHandler;
    }

    public function getHandler()
    {
        if ('local_storage' == $this->handler) {
            return $this->localFileHandler;
        }
    }

    /**
     * Called on App\Topnode\BaseBundle\DependencyInjection\TopnodeFileExtension
     * to parse all the configuration from config.yml and inject here.
     */
    public function setConfig(array $config)
    {
        $this->handler = $config['file_handler'];
    }
}
