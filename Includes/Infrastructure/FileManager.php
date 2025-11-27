<?php

declare(strict_types=1);

namespace Infrastructure;

use Debug\Debug;
use Debug\MessageType;

abstract class FileManager
{
    use Debug;

    final public static function loadAllFromDir(string $dir, string $fileNameExtension, bool $message): void
    {
        $files = scandir($dir);
        foreach ($files as $file) {
            if (! str_ends_with($file, $fileNameExtension)) {
                continue;
            }
            require_once $dir.'/'.$file;
            if ($message) {
                echo self::createUpdateMessage('', 'required '.$file, MessageType::INFO), PHP_EOL;
            }
        }
    }

    /**
     * Check if a file is included
     */
    final public static function isIncluded(string $filePath): bool
    {
        return in_array($filePath, get_included_files());
    }

    final public static function getAllFiles(?string $strip = null): array
    {
        if ($strip) {
            $files = get_included_files();
            $counter = count($files);
            for ($i = 0; $i < $counter; $i++) {
                $files[$i] = str_replace($strip, '', $files[$i]);
            }

            return $files;
        }

        return get_included_files();
    }
}
