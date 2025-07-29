<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\PlayingCards;

final class Card 
{
    public function __construct(public readonly CardSuit $suit, public readonly CardFace $face) {}

    public function asUnicode(): string
    {
        return PlayingCardUnicode::fromCard($this);
    }
}