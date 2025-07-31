<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Games\BlackJack;

use Gamba\CoinGame\GameInstance;
use Gamba\CoinGame\Tools\PlayingCards\CardDeck;

final class BlackJack extends GameInstance
{
    private CardDeck $deck;
    private readonly int $bet;
    private int $wonBet;
    private array $dealerHand = [
        'hidden' => [],
        'visible' => [],
    ];
    private array $playerHand = [];

    public function __construct(int $bet)
    {
        parent::__construct();

        $this->bet = $bet;
        $this->deck = new CardDeck(size: 52);

        $this->playerHand[] = $this->deck->pickCard();
        $this->dealerHand['hidden'][] = $this->deck->pickCard();
        $this->playerHand[] = $this->deck->pickCard();
        $this->dealerHand['visible'][] = $this->deck->pickCard();
    }

    public function hit(): void
    {
        $this->playerHand[] = $this->deck->pickCard(); //Gives the player a new card.
    }

    public function stand(): void
    {
        $this->dealerHand['visible'][] = $this->deck->pickCard(); //Stops the current round and lets the dealer get dealt his cards.
    }

    public function double(): void
    {
        $this->playerHand[] = $this->deck->pickCard(); //Give the player a new card without the possibility to gain more cards, in order to gain triple the initial bet.
    }

    public function split(): void
    {
        //Create new hand with the split card. If the cards have the same card face.
    }
}