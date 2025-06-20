<?php

namespace Gamba\Loot;

use Exception;
use Gamba\Loot\Item\Item;
use Gamba\Loot\Item\ItemCollection;
use Gamba\Loot\Rarity;

/**
 * Pity needed for guaranteed gold item
 */
define('GOLD_PITY_CAP', 80);

/**
 * Pity needed for guaranteed purple item
 */
define('PURPLE_PITY_CAP', 10);

/**
 * Gold chance will increase with this value 
 */
define('GOLD_SOFT_PITY', (int)floor(GOLD_PITY_CAP * 0.92));

/**
 * Max value in mt_rand(1, **PROB_MAX**)
 */
define('PROB_MAX', 10000);

/**
 * Value to be added every roll after **GOLD_SOFT_PITY**
 */
define('SOFT_PITY_ADDER', 0.1 * PROB_MAX);

/**
 * Value that will adjust the rng ranges
 * 
 * @param int $value    goldPity after **GOLD_SOFT_PITY**
 * 
 * @return int  Value that will adjust the rng ranges
 */
define('GOLD_RANGE_ADJUSTER', static fn(int $value) : int => ($value - GOLD_SOFT_PITY) * SOFT_PITY_ADDER);

/**
 * Min blue roll
 */
define('BLUE_MIN', 1);

/**
 * Max blue roll
 */
define('BLUE_MAX', 9200);

/**
 * Min purple roll
 */
define('PURPLE_MIN', 9201);

/**
 * Max purple roll
 */
define('PURPLE_MAX', 9800);

/**
 * Min gold roll
 */
define('GOLD_MIN', 9801);

/**
 * Max gold roll (equal to **PROB_MAX**)
 */
define('GOLD_MAX', PROB_MAX);

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