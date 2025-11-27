<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Games\BlackJack;

use Countable;
use Gamba\CoinGame\Tools\PlayingCards\Card;
use Gamba\CoinGame\Tools\PlayingCards\CardCollection;
use Gamba\CoinGame\Tools\PlayingCards\CardFace;
use LogicException;
use Stringable;

final class Hand implements Countable, Stringable
{
    public private(set) bool $playable = true;

    public private(set) bool $double = false;

    /**
     * @var CardCollection<int, Card>
     */
    public private(set) CardCollection $cards;

    public function __construct(private readonly bool $dealer = false)
    {
        $this->cards = new CardCollection(11);
    }

    public function __toString(): string
    {
        $string = '';

        foreach ($this->cards->yield() as $key => $card) {
            if ($this->dealer && $key === 0) {
                $string .= '[??] ';

                continue;
            }

            $string .= '['.$card->asString().'] ';
        }

        return $string;
    }

    /**
     * If you can split: return **CardCollection[1]** and remove it from the hand
     */
    public function removeForSplit(): Card
    {
        $card = $this->cards[1];
        unset($this->cards[1]);

        return $card;
    }

    public function getFullHandString(): string
    {
        $string = '';
        foreach ($this->cards->yield() as $card) {
            $string .= '['.$card->asString().'] ';
        }

        return $string;
    }

    public function addCard(Card $card): void
    {
        if ($this->playable === false) {
            throw new LogicException('Cannot add card to a locked hand');
        }

        $this->cards->insert($card);

        if ($this->getValue() >= 21) {
            $this->lock();
        }
    }

    /**
     * Get value of the cards in the hand
     *
     * @return int Value of hand
     */
    public function getValue(): int
    {
        $value = 0;
        $aceCount = 0;
        foreach ($this->cards->yield() as $card) {

            $cardValue = $this->getCardValue($card);
            if ($cardValue === 11) {
                $aceCount++;
            }
            $value += $cardValue;
        }

        for ($i = 0; $i < $aceCount; $i++) {
            if ($value > 21) {
                $value -= 10;
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
        $cardCount = 0;
        foreach ($this->cards->yield() as $card) {

            if ($card !== null) {
                $cardCount++;
            }
        }

        return $cardCount;
    }

    public function double(): void
    {
        $this->double = true;
    }

    private function getCardValue(Card $card): int
    {
        return match ($card->face) {
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
            CardFace::ACE => 11,
        };
    }
}
