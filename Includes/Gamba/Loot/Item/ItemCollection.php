<?php

declare(strict_types = 1);

namespace Gamba\Loot\Item;

use Infrastructure\SimpleArray;
use Gamba\Loot\Item\Item;

final class ItemCollection extends SimpleArray {
    public function __construct(int $size) {
        parent::__construct(Item::class, $size);
    }

    public function totalValue() : int {
        $value = 0;
        foreach($this as $item) {
            $value += $item->rarity->getPrice();
        }
        return $value;
    }
}