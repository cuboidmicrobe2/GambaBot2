<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Games\BlackJack;

enum HandResult
{
    case LOSS;
    case WIN;
    case DOUBLE_WIN;
    case TIE;
}