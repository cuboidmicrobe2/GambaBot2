<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Games\Roulette;

enum Color: int
{
    case BLACK = 1;
    case RED = 2;
    case GREEN = 3;

    public static function getFromRoll(int $roll): Color
    {
        return match (true) {
            ($roll === 0) => Color::GREEN,
            ($roll % 2 === 0) => Color::BLACK,
            default => Color::RED
        };
    }

    public function isMatch(int $colorInt): bool
    {
        return $this->value === $colorInt;
    }
}
