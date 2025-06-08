<?php

namespace Gamba\Loot;

enum Rarity : int {
    case BLUE = 1;
    case PURPLE = 2;
    case GOLD = 3;

    public function getPrice() : int {
        return match($this) {
            Rarity::BLUE => 2,
            Rarity::PURPLE => 13,
            Rarity::GOLD => 157,
        };
    }
}