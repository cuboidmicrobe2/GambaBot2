<?php

declare(strict_types=1);

namespace Gamba\Loot;

enum Rarity: int
{
    case BLUE = 1;
    case PURPLE = 2;
    case GOLD = 3;

    public function getPrice(): int
    {
        return (int) match ($this) {
            Rarity::BLUE => WISH_PRICE * 0.4,
            Rarity::PURPLE => WISH_PRICE * 2.6,
            Rarity::GOLD => WISH_PRICE * 12,
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            Rarity::BLUE => '00A2E8',
            Rarity::PURPLE => 'E767E8',
            Rarity::GOLD => 'FFC90E',
            default => 'FFFFFF'
        };
    }
}
