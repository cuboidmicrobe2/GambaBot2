<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\PlayingCards;

final readonly class Card
{
    public function __construct(public CardSuit $suit, public CardFace $face) {}

    public static function newRandom(): self
    {
        $suits = CardSuit::cases();
        $faces = CardFace::cases();

        return new self($suits[array_rand($suits)], $faces[array_rand($faces)]);
    }

    public function asUnicode(): string
    {
        return PlayingCardUnicode::fromCard($this);
    }

    public function asString(string $separator = '', bool $emoji = false): string
    {
        return $this->face->getSymbol().$separator.$this->suit->getSymbol(emoji: $emoji);
    }
}
