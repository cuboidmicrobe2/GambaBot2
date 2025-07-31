<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\PlayingCards;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

final class CardDeck implements ArrayAccess, Countable, IteratorAggregate
{
    private array $cards;
    private readonly array $deckBp;

    private int $cardIterator = 0;

    public function __construct(int $size = 52)
    {
        $addedCards = 0;
        $tempDeck = [];
        while ($addedCards < $size) {
            foreach (CardSuit::cases() as $suit) {
                foreach (CardFace::cases() as $face) {
                    $this->cards[] = new Card($suit, $face);
                }
            }

            $this->shuffle();

            foreach ($this->cards as $card) {
                if ($addedCards >= $size) {
                    continue;
                }
                
                $tempDeck[] = $card;
                $addedCards++;
            }

            $this->cards = [];
        }
        $this->deckBp = $tempDeck;
        $this->resetDeck();
    }

    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    /**
     * Pick a **Card** form the deck and remove it from the deck.
     * 
     * @return null|Card    Returns a **Card** or null if **CardDeck** is empty
     */
    // #[NoDiscard]
    public function pickCard(): ?Card
    {
        if (empty($this->cards)) { 
            return null;
        }

        $key = array_key_first($this->cards);
        $card = $this->cards[$key];
        unset($this->cards[$key]);

        return $card;
    }

    public function nextCard(bool $shuffleOnEnd = false): Card
    {
        if (! isset($this->cards[$this->cardIterator])) {
            $this->cardIterator = 0;

            if ($shuffleOnEnd) {
                $this->shuffle();
            }
        }

        $card = $this->cards[$this->cardIterator];
        $this->cardIterator++;

        return $card;
    }

    public function resetDeck(): void
    {
        $this->cards = $this->deckBp;
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
