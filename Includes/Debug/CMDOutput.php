<?php

declare(strict_types=1);

namespace Debug;

use Stringable;

enum CMD_FONT_COLOR: int
{
    case BLACK = 30;
    case RED = 31;
    case GREEN = 32;
    case YELLOW = 33;
    case BLUE = 34;
    case MAGENTA = 35;
    case CYAN = 36;
    case WHITE = 37;
    case BRIGHT_BLACK = 90;
    case BRIGHT_RED = 91;
    case BRIGHT_GREEN = 92;
    case BRIGHT_YELLOW = 93;
    case BRIGHT_BLUE = 94;
    case BRIGHT_MAGENTA = 95;
    case BRIGHT_CYAN = 96;
    case BRIGHT_WHITE = 97;
}

enum CMD_BACKGROUND_COLOR: int
{
    case BLACK = 40;
    case RED = 41;
    case GREEN = 42;
    case YELLOW = 43;
    case BLUE = 44;
    case MAGENTA = 45;
    case CYAN = 46;
    case WHITE = 47;
    case BRIGHT_BLACK = 100;
    case BRIGHT_RED = 101;
    case BRIGHT_GREEN = 102;
    case BRIGHT_YELLOW = 103;
    case BRIGHT_BLUE = 104;
    case BRIGHT_MAGENTA = 105;
    case BRIGHT_CYAN = 106;
    case BRIGHT_WHITE = 107;
}

final class CMDOutput implements Stringable
{
    private string $output = '';

    public function __toString(): string
    {
        return $this->output;
    }

    public static function new(): self
    {

        return new self();

    }

    public function add(string $text, ?CMD_FONT_COLOR $fg = null, ?CMD_BACKGROUND_COLOR $bg = null): self
    {

        $this->output .= "\x1B[".$fg?->value.'m'.$text."\033[0m";

        return $this;
    }
}
