<?php

namespace Gamba\CoinGame\Roulette;

use Gamba\CoinGame\Roulette\Color;

abstract class Roulette {

    public static function roll() : int {
        return mt_rand(0, 38);
    }

    public static function getColor(int $roll) : Color {
        return match(true) {
            ($roll == 0) => Color::GREEN,
            ($roll % 2 == 0) => Color::BLACK,
            default => Color::RED
        };
    }
}