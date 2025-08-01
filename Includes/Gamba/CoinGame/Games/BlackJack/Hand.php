<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Games\BlackJack;

use Exception;
use Gamba\CoinGame\Tools\PlayingCards\Card;
use Gamba\CoinGame\Tools\PlayingCards\CardCollection;
use Gamba\CoinGame\Tools\PlayingCards\CardFace;

final class Hand
{
    private CardCollection $cards;

    public private(set) bool $playable = true;

    public function __construct()
    {
        $this->cards = new CardCollection(11);
    }

    public function __toString(): string
    {
        $string = '';

        /** @var Card $card */
        foreach ($this->cards as $card) {
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
        foreach ($this->cards as $card) {
            $value += $this->getCardValue($card);
        }

        return $value;
    }
    
    public function lock(): void
    {
        $this->playable = false;
    }

    private function getCardValue(Card $card): int
    {
        return match ($card->face) {
            CardFace::ACE => 1,
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