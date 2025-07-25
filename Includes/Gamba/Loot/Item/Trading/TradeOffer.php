<?php

declare(strict_types=1);

namespace Gamba\Loot\Item\Trading;

use Gamba\Loot\Item\ItemCollection;

final class TradeOffer
{
    private const int LIFE_TIME = 600;

    private int $age = 0;

    public function __construct(
        public readonly string $from,
        public readonly string $to,
        public readonly ItemCollection $formItems,
        public readonly ItemCollection $toItems,

    ) {
        $this->age = time();
    }

    public function renew(): void
    {
        $this->age = time();
    }

    public function isOld(): bool
    {
        return time() - $this->age >= self::LIFE_TIME;
    }
}
