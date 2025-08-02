<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Games\BlackJack;

use Gamba\CoinGame\GameInstance;
use Gamba\CoinGame\Tools\PlayingCards\Card;
use Gamba\CoinGame\Tools\PlayingCards\CardDeck;
use React\EventLoop\Loop;

final class BlackJack extends GameInstance
{
    private CardDeck $deck;
    public private(set) int $wonBet;

    private Hand $dealerHand;

    private array $playerHands = [];
    private int $handIterator = 0;

    public function __construct(public private(set) readonly int $bet)
    {
        parent::__construct();

        $this->deck = new CardDeck(size: 52);
        $this->playerHands[$this->handIterator] = new Hand;
        $this->dealerHand = new Hand(dealer: true);

        $this->playerHands[$this->handIterator]->addCard($this->deck->pickCard());
        $this->dealerHand->addcard($this->deck->pickCard());
        $this->playerHands[$this->handIterator]->addCard($this->deck->pickCard());
        $this->dealerHand->addcard($this->deck->pickCard());
    }

    public function hit(): void
    {
        $this->renew();
        $this->playerHands[$this->handIterator]->addCard($this->deck->pickCard()); //Gives the player a new card.

        $this->advanceIterator();
    }

    public function stand(): void
    {
        $this->renew();
        $this->playerHands[$this->handIterator]->lock();
        $this->dealerHand['visible'][] = $this->deck->pickCard(); //Stops the current round and lets the dealer get dealt his cards.

        $this->advanceIterator();
    }

    public function double(): void
    {
        $this->renew();
        $this->playerHands[$this->handIterator]->addCard($this->deck->pickCard()); //Give the player a new card without the possibility to gain more cards, in order to gain triple the initial bet.
        $this->playerHands[$this->handIterator]->lock();

        $this->advanceIterator();
    }

    public function split(): void
    {
        $this->renew();


        $this->playerHands[] = new Hand;

        //Create new hand with the split card. If the cards have the same card face.

        $this->advanceIterator();
    }

    public function splitCheck(): bool
    {
        if (count($this->playerHands[$this->handIterator]) > 2) {
            return false;
        }
        $card1 = $this->playerHands[$this->handIterator]->cards[0];
        $card2 = $this->playerHands[$this->handIterator]->cards[1];
        if ($card1->face === $card2->face) { 
            return true;
        }
        return false;
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

    public function isBlackJack(): bool
    {
        return ($this->dealerHand->getValue() >= 21);
    }

    public function dealDealer(): void
    {
        $dealLoop = true;
        while ($dealLoop) {
            $this->dealerHand->addcard($this->deck->pickCard());
            if ($this->dealerHand->getValue() < 17) {
                $dealLoop = false;
            }
        }
    }
    
    public function calcResult(): array
    {
        $dealerValue = $this->dealerHand->getValue();
        $result = [];
        /** @var Hand $hand */
        foreach ($this->playerHands as $hand) {
            $handValue = $hand->getValue();
            if ($handValue > $dealerValue || $handValue < 21) {
                $result[] = false;
                continue;
            }
            $result[] = true;
        }
        
        return $result;
    }

    private function advanceIterator(): void
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