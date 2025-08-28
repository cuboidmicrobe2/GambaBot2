<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\Players;

use Gamba\Loot\Item\Inventory;

final class Player
{
    public function __construct(
        public readonly string $uid, 
        public readonly Inventory $inventory, 
        public ?array $data
    ) {}
}