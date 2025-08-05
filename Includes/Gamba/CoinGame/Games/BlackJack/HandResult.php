<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Games\BlackJack;

enum HandResult
{
    case WIN;
    case DOUBLE_WIN;
    case LOSS;
    case DOUBLE_LOSS;
    case TIE;
}