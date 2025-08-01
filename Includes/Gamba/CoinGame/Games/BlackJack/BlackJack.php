<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Games\BlackJack;

use Gamba\CoinGame\GameInstance;
use Gamba\CoinGame\Tools\PlayingCards\CardDeck;

final class BlackJack extends GameInstance
{
    private CardDeck $deck;
    public private(set) int $wonBet;

    private array $dealerHand = [
        'hidden' => [],
        'visible' => [],
    ];
    private array $playerHand = []; // make hand obj

    private array $playerHands = [];
    private int $handIterator = 0;

    public function __construct(public private(set) readonly int $bet)
    {
        parent::__construct();

        $this->deck = new CardDeck(size: 52);
        $this->playerHands[$this->handIterator] = new Hand;
        $this->playerHands[$this->handIterator]->addCard($this->deck->pickCard());

        // $this->playerHand[] = $this->deck->pickCard();
        $this->dealerHand['hidden'][] = $this->deck->pickCard();
        $this->playerHands[$this->handIterator]->addCard($this->deck->pickCard());
        // $this->playerHand[] = $this->deck->pickCard();
        $this->dealerHand['visible'][] = $this->deck->pickCard();
    }

    public function hit(): void
    {
        $this->playerHand[] = $this->deck->pickCard(); //Gives the player a new card.

        $this->advanceInterator();
    }

    public function stand(): void
    {
        $this->playerHands[$this->handIterator]->lock();
        $this->dealerHand['visible'][] = $this->deck->pickCard(); //Stops the current round and lets the dealer get dealt his cards.

        $this->advanceInterator();
    }

    public function double(): void
    {
        $this->playerHands[$this->handIterator]->addCard($this->deck->pickCard()); //Give the player a new card without the possibility to gain more cards, in order to gain triple the initial bet.
        $this->playerHands[$this->handIterator]->lock();

        $this->advanceInterator();
    }

    public function split(): void
    {
        $this->playerHands[] = new Hand;
        //Create new hand with the split card. If the cards have the same card face.

        $this->advanceInterator();
    }

    /**
     * Check if the player has any hands left
     */
    public function playableHands(): bool
    {
        /** @var Hand $hand */
        foreach ($this->playerHands as $hand) {
            if ($hand->playable === true) {
                return true;
            }
        }

        return false;
    }

    private function advanceInterator(): void
    {
        $lookingForHand = $this->playableHands();
        while ($lookingForHand) {
            if (! isset($this->playerHands[$this->handIterator])) {
                $this->handIterator = 0;
            }

            if ($this->playerHands[$this->handIterator]->playable === true) {
                $lookingForHand = false;
            }

            $this->handIterator++;
        }   
    }
}