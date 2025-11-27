<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\PlayingCards;

enum CardFace
{
    case ACE;
    case TWO;
    case THREE;
    case FOUR;
    case FIVE;
    case SIX;
    case SEVEN;
    case EIGHT;
    case NINE;
    case TEN;
    case JACK;
    case QUEEN;
    case KING;

    public function getSymbol(): string
    {
        return match ($this) {
            self::TWO => '2',
            self::THREE => '3',
            self::FOUR => '4',
            self::FIVE => '5',
            self::SIX => '6',
            self::SEVEN => '7',
            self::EIGHT => '8',
            self::NINE => '9',
            self::TEN => '10',
            self::JACK => 'J',
            self::QUEEN => 'Q',
            self::KING => 'K',
            self::ACE => 'A',
        };
    }
}
