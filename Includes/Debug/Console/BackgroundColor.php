<?php

declare(strict_types=1);

namespace Debug\Console;

enum BackgroundColor: int
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
