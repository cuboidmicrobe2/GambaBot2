<?php

declare(strict_types = 1);

namespace Gamba\Loot;

use Exception;
use Gamba\Loot\Item\Item;
use Gamba\Loot\Item\ItemCollection;
use Gamba\Loot\Rarity;

abstract class Decide {
    
    public static function rarity(int &$goldPity, int &$purplePity) : Rarity {

        $goldPity++;
        $purplePity++;

        if($purplePity >= PURPLE_PITY_CAP) {
            $purplePity = 0;
            return Rarity::PURPLE;
        } 
        elseif($goldPity > GOLD_PITY_CAP) {
            $goldPity = 0;
            return Rarity::GOLD;
        }

        $probValue = mt_rand(1, PROB_MAX);
        $adjustmet = ($goldPity > GOLD_SOFT_PITY) ? call_user_func(GOLD_RANGE_ADJUSTER, $goldPity) : 0;

        if($probValue >= (GOLD_MIN - $adjustmet)) {
            $goldPity = 0;
            return Rarity::GOLD;
        }
        elseif($probValue >= (PURPLE_MIN - $adjustmet) AND $probValue <= (PURPLE_MAX - $adjustmet)) {
            $purplePity = 0;
            return Rarity::PURPLE;
        }

        return Rarity::BLUE;
    }

    public static function fromCollection(ItemCollection $items) : Item {
        $randKey = mt_rand(0, $items->size-1);
        return $items[$randKey];
    }
}