<?php

namespace Gamba\Loot\Item;

use Infrastructure\SimpleArray;
use Gamba\Loot\Item\Item;

final class ItemCollection extends SimpleArray {
    public function __construct(int $size) {
        parent::__construct(Item::class, $size);
    }
}