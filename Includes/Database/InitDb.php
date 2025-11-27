<?php

declare(strict_types=1);

echo __DIR__, PHP_EOL;

$rootDir = str_replace('\Includes\Database', '', __DIR__);
echo $rootDir;
exit;
