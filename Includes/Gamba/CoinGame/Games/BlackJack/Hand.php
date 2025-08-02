<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Games\BlackJack;

use Countable;
use Exception;
use Gamba\CoinGame\Tools\PlayingCards\Card;
use Gamba\CoinGame\Tools\PlayingCards\CardCollection;
use Gamba\CoinGame\Tools\PlayingCards\CardFace;
use Stringable;

final class Hand implements Countable, Stringable
{
    private const int ACE = 710;

    private const int ACE_MAX = 11;

    private const int ACE_MIN = 1;

    public private(set) bool $playable = true;

    public private(set) CardCollection $cards;

    public function __construct(private readonly bool $dealer = false)
    {
        $this->cards = new CardCollection(11);
    }

    public function __toString(): string
    {
        $string = '';

        /**
         * @var int $key
         * @var Card $card
         */
        foreach ($this->cards as $key => $card) {
            if ($this->dealer && $key === 0) {
                $string .= '?? ';
                continue;
            }

            $string .= $card->asString(emoji: true).' ';
        }

        return $string;
    }

    public function addCard(Card $card): void
    {
        if ($this->playable === false) {
            throw new Exception('Cannot add card to a locked hand');
        }

        $this->cards->insert($card);
    }

    public function getValue(): int
    {
        $value = 0;
        $aceCount = 0;
        foreach ($this->cards as $card) {
            $cardValue = $this->getCardValue($card);
            if ($cardValue === self::ACE) {
                $aceCount++;

                continue;
            }
            $value += $cardValue;
        }

        for ($i = 0; $i < $aceCount; $i++) {
            if (($value + self::ACE_MAX) > 21) {
                $value += self::ACE_MIN;
            } else {
                $value += self::ACE_MAX;
            }
        }

        return $value;
    }

    public function lock(): void
    {
        $this->playable = false;
    }

    public function count(): int
    {
        return count($this->cards);
    }

    private function getCardValue(Card $card): int
    {
        return match ($card->face) {
            CardFace::ACE => self::ACE,
            CardFace::TWO => 2,
            CardFace::THREE => 3,
            CardFace::FOUR => 4,
            CardFace::FIVE => 5,
            CardFace::SIX => 6,
            CardFace::SEVEN => 7,
            CardFace::EIGHT => 8,
            CardFace::NINE => 9,
            CardFace::TEN => 10,
            CardFace::JACK => 10,
            CardFace::QUEEN => 10,
            CardFace::KING => 10,
        };
    }
}
