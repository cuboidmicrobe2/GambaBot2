<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Games\HighLow;

use Gamba\CoinGame\Tools\Players\Player;
use Gamba\CoinGame\Tools\PlayingCards\Card;
use Gamba\CoinGame\Tools\PlayingCards\CardDeck;
use Gamba\CoinGame\Tools\PlayingCards\CardFace;

final class HigherLower
{
    private const float MOD_WIN = 0.6;

    private const float MOD_LOSS = 0.7;

    public private(set) Card $currentCard;

    public private(set) int $score;

    public private(set) bool $gameEnd = false;

    private readonly CardDeck $cards;

    /**
     * @var array<int, Guess>
     */
    private array $guessHistory = [];

    public function __construct(public readonly Player $player, public readonly int $bet)
    {
        $this->cards = new CardDeck;
        $this->currentCard = $this->cards->pickCard();
        $this->score = $this->bet;
    }

    public function makeGuess(Guess $guess): bool
    {
        $this->guessHistory[] = $guess;
        $newCard = $this->cards->pickCard();

        $newCardValue = $this->evalCard($newCard);
        $currentCardValue = $this->evalCard($this->currentCard);

        $correctGuess = match (true) {
            ($newCardValue > $currentCardValue) => Guess::HIGHER,
            ($newCardValue < $currentCardValue) => Guess::LOWER,
            default => Guess::SAME,
        };

        if ($guess === $correctGuess || $correctGuess === Guess::SAME) {
            $this->score += $this->bet * self::MOD_WIN;
            $correct = true;
        } else {
            $this->score -= $this->bet * self::MOD_LOSS;
            $correct = false;
        }

        $this->currentCard = $newCard;

        if (count($this->cards) < 1 || $this->score < 0) {
            $this->gameEnd = true;
        }

        return $correct;
    }

    public function getGuessHistoryString(): string
    {
        $string = '';

        foreach ($this->guessHistory as $guess) {
            $string .= $guess->getEmoji();
        }

        return $string;
    }

    public function endOfGameLogic(): void
    {

        $this->player->inventory->addCoins($this->score);
    }

    private function evalCard(Card $card): int
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
