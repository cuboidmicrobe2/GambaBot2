<?php

declare(strict_types=1);

namespace Debug\Console;

use ReflectionClass;
use Stringable;

final class CMDOutput implements Stringable
{
    private string $output = '';

    public function __construct(string|FontColor ...$message)
    {
        $counter = count($message);
        for ($i = 0; $i < $counter; $i++) {
            $current = $message[$i];
            if ($current instanceof FontColor) {
                $next = $message[++$i] ?? '';
                $this->output .= "\x1B[".$current->value.'m'.$next."\033[0m";
            } else {
                $this->output .= "\x1B[m".$message[$i]."\033[0m";
            }
        }
    }
    
    

    public function __toString(): string
    {
        return $this->output;
    }

    public static function new(): self
    {
        return new ReflectionClass(self::class)->newInstanceWithoutConstructor();
    }

    public static function create(string|FontColor ...$message): self
    {
        return new self(...$message);
    }

    public function add(string $text, ?FontColor $fg = null, ?BackgroundColor $bg = null): self
    {

        $this->output .= "\x1B[".$fg?->value.'m'.$text."\033[0m";

        return $this;
    }
}
