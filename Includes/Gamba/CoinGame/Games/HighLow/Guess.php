<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Games\HighLow;

enum Guess
{
    case HIGHER;
    case LOWER;
    case SAME;

    public function getEmoji(): string
    {
        return match ($this) {
            self::HIGHER => '⬆️',
            self::LOWER => '⬇️',
            self::SAME => '➡️',
        };
    }
}
