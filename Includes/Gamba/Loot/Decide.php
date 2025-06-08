<?php

namespace Gamba\Loot;

use Gamba\Loot\Rarity;

abstract class Decide {

    public static function rarity() : Rarity {
        $randVal = mt_rand(1, 100);
        return match(true) {
            $randVal == 100 => Rarity::GOLD,
            $randVal <= 20 => Rarity::PURPLE,
            default => Rarity::BLUE,
        };
    }
    
    public static function from(array $items) /* : Item */ {
        mt_rand(1, $size);
        // get id...
    }

}