<?php

declare(strict_types=1);

const PHP_FILENAME_EXTENSION = '.php';

/**
 * @deprecated Use psr-4 autoloading via composer
 */
final class Autoloader
{
    public const int DO_OUTPUT = 1;

    public function __construct(
        private readonly string $name = 'Autoloader',
        private readonly string $timeZone = 'Europe/London',
        private int $flags = 0,
    ) {}

    // /**
    //  * @param null|string $debugName    Name of output or null for no output.
    //  */
    // public static function configure(string $name = 'Autoloader', int $flags = 0) : self {
    //     return new static($name, $flags);
    // }

    public function start(): void
    {
        spl_autoload_register(function (string $name): void {
            $file = str_replace('\\', '/', $name).PHP_FILENAME_EXTENSION;

            if (($this->flags & self::DO_OUTPUT) !== 0) {
                try {
                    $time = new DateTimeImmutable(timezone: new DateTimeZone($this->timeZone));
                    $formatedTime = $time->format('Y-m-d\TH:i:s.uP');
                } catch (Exception $e) {
                    $formatedTime = $e->getMessage();
                }
            } else {
                $formatedTime = '';
            }

            if (file_exists(__DIR__.'/'.$file)) {
                if (($this->flags & self::DO_OUTPUT) !== 0) {
                    echo '['.$formatedTime.'] '.$this->name.'.INFO: loaded class: '.$name.PHP_EOL;
                }
                require_once $file;
            } else {
                $file ??= 'FILENAME_IS_NULL';
                if (($this->flags & self::DO_OUTPUT) !== 0) {
                    echo '['.$formatedTime.'] '.$this->name.'.WARNING: Could not load class: '.$name.' from: '.$file.PHP_EOL;
                }
            }
        });
    }

    public function doOutput(bool $output): void
    {
        if ($output && $this->flags & self::DO_OUTPUT) {
            $this->flags -= self::DO_OUTPUT;
        } elseif (! $output && ! ($this->flags & self::DO_OUTPUT)) {
            $this->flags += self::DO_OUTPUT;
        }
    }
}
