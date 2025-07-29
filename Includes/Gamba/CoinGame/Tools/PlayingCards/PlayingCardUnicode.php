<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\PlayingCards;

use Gamba\CoinGame\Tools\PlayingCards\CardSuit;
use Gamba\CoinGame\Tools\PlayingCards\CardFace;
use Gamba\CoinGame\Tools\PlayingCards\Card;

abstract class PlayingCardUnicode
{
    private const array SYMBOLS = [
        CardSuit::CLUBS->name => [
            CardFace::ACE->name => '🃑',
            CardFace::TWO->name => '🃒',
            CardFace::THREE->name => '🃓',
            CardFace::FOUR->name => '🃔',
            CardFace::FIVE->name => '🃕',
            CardFace::SIX->name => '🃖',
            CardFace::SEVEN->name => '🃗',
            CardFace::EIGHT->name => '🃘',
            CardFace::NINE->name => '🃙',
            CardFace::TEN->name => '🃚',
            CardFace::JACK->name => '🃛',
            CardFace::QUEEN->name => '🃝',
            CardFace::KING->name => '🃞',
        ],
        CardSuit::DIAMONDS->name => [
            CardFace::ACE->name => '🃁',
            CardFace::TWO->name => '🃂',
            CardFace::THREE->name => '🃃',
            CardFace::FOUR->name => '🃄',
            CardFace::FIVE->name => '🃅',
            CardFace::SIX->name => '🃆',
            CardFace::SEVEN->name => '🃇',
            CardFace::EIGHT->name => '🃈',
            CardFace::NINE->name => '🃉',
            CardFace::TEN->name => '🃊',
            CardFace::JACK->name => '🃋',
            CardFace::QUEEN->name => '🃍',
            CardFace::KING->name => '🃎',
        ],
        CardSuit::HEARTS->name => [
            CardFace::ACE->name => '🂱',
            CardFace::TWO->name => '🂲',
            CardFace::THREE->name => '🂳',
            CardFace::FOUR->name => '🂴',
            CardFace::FIVE->name => '🂵',
            CardFace::SIX->name => '🂶',
            CardFace::SEVEN->name => '🂷',
            CardFace::EIGHT->name => '🂸',
            CardFace::NINE->name => '🂹',
            CardFace::TEN->name => '🂺',
            CardFace::JACK->name => '🂻',
            CardFace::QUEEN->name => '🂽',
            CardFace::KING->name => '🂾',
        ],
        CardSuit::SPADES->name => [
            CardFace::ACE->name => '🂡',
            CardFace::TWO->name => '🂢',
            CardFace::THREE->name => '🂣',
            CardFace::FOUR->name => '🂤',
            CardFace::FIVE->name => '🂥',
            CardFace::SIX->name => '🂦',
            CardFace::SEVEN->name => '🂧',
            CardFace::EIGHT->name => '🂨',
            CardFace::NINE->name => '🂩',
            CardFace::TEN->name => '🂪',
            CardFace::JACK->name => '🂫',
            CardFace::QUEEN->name => '🂭',
            CardFace::KING->name => '🂮',
        ],
    ];

    public static function fromCard(Card $card): string
    {
        return self::SYMBOLS[$card->suit->name][$card->face->name];
    }
}