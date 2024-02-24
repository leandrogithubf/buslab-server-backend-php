<?php

namespace App\Buslab\Utils;

class LogCreator
{
    public static function create (string $name, string $message): void
    {
        $rootPath = __DIR__ . '/../../../';
        $logFilePath = "{$rootPath}/var/log/{$name}.log";

        if (file_exists($logFilePath)) {
            // Log file exists, append message to end of file
            file_put_contents($logFilePath, date("Y-m-d H:i:s") . " " . $message . "\n", FILE_APPEND);
        } else {
            // Log file does not exist, create new file with message
            file_put_contents($logFilePath, date("Y-m-d H:i:s") . " " . $message . "\n");
        }
    }
}