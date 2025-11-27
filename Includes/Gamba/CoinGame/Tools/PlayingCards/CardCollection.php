<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\PlayingCards;

use Infrastructure\SimpleArray;

final class CardCollection extends SimpleArray
{
    public function __construct(int $size)
    {
        parent::__construct(Card::class, $size);
    }
}
