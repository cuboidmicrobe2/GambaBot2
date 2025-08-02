<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Games\BlackJack;

use Gamba\CoinGame\GameInstance;
use Gamba\CoinGame\Tools\PlayingCards\CardDeck;
use Gamba\CoinGame\Games\BlackJack\Hand;
use LogicException;

final class BlackJack extends GameInstance
{

    /**
     * @var CardDeck<int, Card>
     */
    private CardDeck $deck;

    public private(set) int $wonBet; // pointless? 

    private Hand $dealerHand;

    /**
     * @var Hand[]
     */
    private array $playerHands = [];

    private int $handIterator = 0;

    public function __construct(public readonly int $bet, int $decks)
    {
        if ($decks <= 0) {
            throw new LogicException('Cannot play '.self::class.' with '.$decks. ' decks');
        }

        parent::__construct();

        $cardCount = 52 * $decks;
        $this->deck = new CardDeck($cardCount);
        $this->playerHands[$this->handIterator] = new Hand;
        $this->dealerHand = new Hand(dealer: true);

        $this->playerHands[$this->handIterator]->addCard($this->deck->pickCard());
        $this->dealerHand->addcard($this->deck->pickCard());
        $this->playerHands[$this->handIterator]->addCard($this->deck->pickCard());
        $this->dealerHand->addcard($this->deck->pickCard());
    }

    /**
     * Gives the player a new card.
     */
    public function hit(): void
    {
        $this->renew();
        $this->playerHands[$this->handIterator]->addCard($this->deck->pickCard()); //Gives the player a new card.

        $this->advanceIterator();
    }

    /**
     * Locks current hand
     */
    public function stand(): void
    {
        $this->renew();
        $this->playerHands[$this->handIterator]->lock();

        $this->advanceIterator();
    }

    /**
     * Give the player a new card without the possibility to gain more cards, in order to gain triple the initial bet.
     */
    public function double(): void
    {
        $this->renew();
        $this->playerHands[$this->handIterator]->addCard($this->deck->pickCard());
        $this->playerHands[$this->handIterator]->double();
        $this->playerHands[$this->handIterator]->lock();

        $this->advanceIterator();
    }

    /**
     * Create new hand with the split card. If the cards have the same card face.
     */
    public function split(): void
    {
        $this->renew();

        if (! $this->splitCheck()) {
            throw new LogicException('Cannot split on this '.Hand::class);
        }

        $card = $this->playerHands[$this->handIterator]->removeForSplit();
        $this->playerHands[$this->handIterator]->addCard($this->deck->pickCard());

        $newHand = new Hand();
        $newHand->addCard($card);
        $newHand->addCard($this->deck->pickCard());


        $this->playerHands[] = $newHand;

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
        foreach ($this->playerHands as $hand) {
            if ($hand->playable === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the dealer has Black Jack
     */
    public function dealerBlackJack(): bool
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
    
    /**
     * Get an array of bools for the result of every hand
     * 
     * @return array<int, HandResult>
     */
    public function calcResult(): array
    {
        $dealerValue = $this->dealerHand->getValue();
        $result = [];

        foreach ($this->playerHands as $hand) {
            $handValue = $hand->getValue();
            if ($handValue > $dealerValue || $handValue < 21) {
                $result[] = HandResult::LOSS;
                continue;
            }
            if ($handValue === $dealerValue) {
                $result[] = HandResult::TIE;
                continue;
            }
            if ($hand->double) {
                $result[] = HandResult::DOUBLE_WIN;
                continue;
            }
            $result[] = HandResult::WIN;
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