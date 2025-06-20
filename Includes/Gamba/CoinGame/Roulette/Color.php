<?php

namespace Gamba\CoinGame\Roulette;

enum Color : int {
    case BLACK = 1;
    case RED = 2;
    case GREEN = 3;

    public function isMatch(int $colorInt) : bool {
        return ($this->value == $colorInt);
    }
}