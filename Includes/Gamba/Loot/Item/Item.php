<?php

namespace Gamba\Loot\Item;

use Gamba\Loot\Rarity;
use JsonSerializable;

final readonly class Item implements JsonSerializable {

    public function __construct(
        public string $name,
        public Rarity $rarity,
        public int $id
    ) {}

    public function jsonSerialize(): array {
        return get_object_vars($this);
    }
}