<?php

namespace App\Topnode\FileBundle\Utils;

interface FileManipulatorInterface
{
    public function upload($file, $directory);

    public function download($completePath, $downloadName);

    public function view($completePath, $viewName);

    public function content($completePath);
}
