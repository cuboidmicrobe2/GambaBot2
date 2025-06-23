<?php

namespace Gamba\Loot;

enum Rarity : int {
    case BLUE = 1;
    case PURPLE = 2;
    case GOLD = 3;

    public function getPrice() : int {
        return match($this) {
            Rarity::BLUE => 2,
            Rarity::PURPLE => 78,
            Rarity::GOLD => 1570,
        };
    }

    function getColor() : string {
        return match($this) {
            Rarity::BLUE => '00A2E8',
            Rarity::PURPLE => 'E767E8',
            Rarity::GOLD => 'FFC90E',
            default => 'FFFFFF'
        };
    }
}