<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\PlayingCards;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Gamba\CoinGame\Tools\PlayingCards\CardSuit;
use Gamba\CoinGame\Tools\PlayingCards\CardFace;
use Gamba\CoinGame\Tools\PlayingCards\Card;
use IteratorAggregate;
use Traversable;

final class CardDeck implements ArrayAccess, IteratorAggregate, Countable
{
    public array $cards;

    private int $cardIterator = 0;

    public function __construct()
    {
        foreach (CardSuit::cases() as $suit) {
            foreach (CardFace::cases() as $face) {
                $this->cards[] = new Card($suit, $face);
            }
        }
    }

    public static function fromCustomSize(int $size): self
    {
        
    }

    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    public function nextCard(bool $shuffleOnEnd = false): Card
    {
        if(!isset($this->cards[$this->cardIterator])) {
            $this->cardIterator = 0;
        }

        $card = $this->cards[$this->cardIterator];
        $this->cardIterator++;
        return $card;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->cards[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->cards[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->cards[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->cards[$offset]);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->cards);
    }

    public function count(): int
    {
        return count($this->cards);
    }
}