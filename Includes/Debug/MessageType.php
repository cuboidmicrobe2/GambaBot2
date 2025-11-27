<?php

declare(strict_types=1);

namespace Debug;

use ReflectionClass;

abstract class MessageType
{
    public const int DEBUG = 1;

    public const int INFO = 2;

    public const int WARNING = 4;

    public const int ATTENTION = 8;

    public const int RETURN = 16;

    public const int PING = 32;

    public const int STATUS = 64;

    public const int USAGE = 128;

    final public static function toString(int $type): string
    {
        return match ($type) {
            1 => '.DEBUG',
            2 => '.INFO',
            4 => '.WARNING',
            8 => '.ATTENTION',
            16 => '.RETURN',
            32 => '.PING',
            64 => '.STATUS',
            128 => '.USAGE',

            default => '.[   ]'
        };
    }

    final public static function allTypes(): int
    {
        $class = new ReflectionClass(self::class);
        $int = 0;
        foreach ($class->getConstants() as $const) {
            $int += $const;
        }

        return $int;
    }
}
