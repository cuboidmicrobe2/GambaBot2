<?php

namespace Gamba\CoinGame\Roulette;

abstract class Roulette {
    public static function roll() : int {
        return mt_rand(0, 38);
    }
}