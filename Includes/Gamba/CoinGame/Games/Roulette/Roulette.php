<?php

declare(strict_types = 1);

namespace Gamba\CoinGame\Games\Roulette;

abstract class Roulette {
    public static function roll() : int {
        return mt_rand(0, 38);
    }
}