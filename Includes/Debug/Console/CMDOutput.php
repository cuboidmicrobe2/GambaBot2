<?php

declare(strict_types=1);

namespace Debug\Console;

use ReflectionClass;
use Stringable;

final class CMDOutput implements Stringable
{
    private const string ESCAPE = "\x1b";

    private array $message;

    public function __construct(string|FontColor ...$message)
    {
        $this->message = $message;
    }

    public function __toString(): string
    {
        $output = '';
        $counter = count($this->message);
        for ($i = 0; $i < $counter; $i++) {
            $current = $this->message[$i];
            if ($current instanceof FontColor) {
                $next = $this->message[++$i] ?? '';
                $output .= self::ESCAPE.'['.$current->value.'m'.$next.self::ESCAPE.'[0m';
            } else {
                $output .= self::ESCAPE.'[m'.$current.self::ESCAPE.'[0m';
            }
        }
        return $output;
    }

    /**
     * Allows shorthanding of add() method. 
     */
    public function __invoke(string $text, ?FontColor $fg = null): self
    {
        $this->add($text, $fg);
        return $this;
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
        if ($fg instanceof FontColor) {
            $this->message[] = $fg;
        }
        
        $this->message[] = $text;

        return $this;
    }

    /**
     * Get message without any color.
     */
    public function asString(): string
    {
        $string = '';
        foreach ($this->message as $part) {
            if (! $part instanceof FontColor) {
                $string .= $part;
            }
        }

        return $string;
    }
}
