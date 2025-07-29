<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\PlayingCards;

enum CardSuit
{
    case CLUBS;
    case HEARTS;
    case SPADES;
    case DIAMONDS;

    public function getSymbol(bool $emoji = false): string
    {
        if ($emoji) {
            return match ($this) {
                self::CLUBS => '♣️',
                self::HEARTS => '♥️',
                self::SPADES => '♠️',
                self::DIAMONDS => '♦️'
            };
        }

        return match ($this) {
            self::CLUBS => '♣',
            self::HEARTS => '♥',
            self::SPADES => '♠',
            self::DIAMONDS => '♦'
        };
    }
}