<?php

declare(strict_types=1);

namespace Debug\Console;

enum FontColor: int
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
