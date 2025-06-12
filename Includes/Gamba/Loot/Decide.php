<?php

namespace Gamba\Loot;

use Gamba\Loot\Item\Item;
use Gamba\Loot\Item\ItemCollection;
use Gamba\Loot\Rarity;

abstract class Decide {

    public static function rarity() : Rarity {
        $randVal = mt_rand(1, 1000);
        return match(true) {
            $randVal == 1000 => Rarity::GOLD,
            $randVal <= 100 => Rarity::PURPLE,
            default => Rarity::BLUE,
        };
    }
    
    public static function fromCollection(ItemCollection $items) : Item {
        $randKey = mt_rand(0, $items->size-1);
        return $items[$randKey];
    }

}