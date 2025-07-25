<?php

declare(strict_types=1);

namespace Gamba\Loot;

use Gamba\Loot\Item\Item;
use Gamba\Loot\Item\ItemCollection;

abstract class Decide
{
    final public static function rarity(int &$goldPity, int &$purplePity): Rarity
    {

        $goldPity++;
        $purplePity++;

        if ($purplePity >= PURPLE_PITY_CAP) {
            $purplePity = 0;

            return Rarity::PURPLE;
        }
        if ($goldPity > GOLD_PITY_CAP) {
            $goldPity = 0;

            return Rarity::GOLD;
        }

        $probValue = mt_rand(1, PROB_MAX);
        $adjustmet = ($goldPity > GOLD_SOFT_PITY) ? call_user_func(GOLD_RANGE_ADJUSTER, $goldPity) : 0;

        if ($probValue >= (GOLD_MIN - $adjustmet)) {
            $goldPity = 0;

            return Rarity::GOLD;
        }
        if ($probValue >= (PURPLE_MIN - $adjustmet) and $probValue <= (PURPLE_MAX - $adjustmet)) {
            $purplePity = 0;

            return Rarity::PURPLE;
        }

        return Rarity::BLUE;
    }

    final public static function fromCollection(ItemCollection $items): Item
    {
        $randKey = mt_rand(0, $items->size - 1);

        return $items[$randKey];
    }
}
