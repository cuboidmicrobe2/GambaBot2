<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Games\RPS;

use Exception;
use Gamba\CoinGame\GameInstance;
use Gamba\Loot\Item\Inventory;
use InvalidArgumentException;

final class RockPaperScissors extends GameInstance
{
    private const int WIN_CON = 3;

    public private(set) int $round = 0;

    public private(set) array $roundData = [];

    public private(set) int $p1Points = 0;

    public private(set) int $p2Points = 0;

    public bool $started = false {
        get {
            return $this->started;
        }
        set(bool $sate) {
            if (! $this->started) {
                $this->started = $sate;
            }
        }
    }

    public function __construct(public private(set) string $p1Uid, private readonly Inventory $p1Inv, public private(set) string $p2Uid, private readonly Inventory $p2Inv, public private(set) int $bet)
    {
        parent::__construct();
        $this->newRound();
    }

    public function __destruct()
    {
        if ($this->started) {
            if ($this->p1Points >= self::WIN_CON) {
                $this->p1Inv->setCoins($this->p1Inv->getCoins() + ($this->bet * 2));

                return;
            }
            if ($this->p2Points >= self::WIN_CON) {
                $this->p2Inv->setCoins($this->p2Inv->getCoins() + ($this->bet * 2));

                return;
            }
            if ($this->roundData[$this->round][$this->p1Uid] !== null && $this->roundData[$this->round][$this->p2Uid] === null) {
                $this->p1Inv->setCoins($this->p1Inv->getCoins() + ($this->bet * 2));

                return;
            }
            if ($this->roundData[$this->round][$this->p2Uid] !== null && $this->roundData[$this->round][$this->p1Uid] === null) {
                $this->p2Inv->setCoins($this->p2Inv->getCoins() + ($this->bet * 2));

                return;
            }

            return; // game has started but no one has made a move this round so both lose :)
        }
        $this->p1Inv->setCoins($this->p1Inv->getCoins() + $this->bet);
        $this->p2Inv->setCoins($this->p2Inv->getCoins() + $this->bet);
    }

    public function makeMove(string $uid, RpsMove $move): bool
    {
        if ($uid !== $this->p1Uid && $uid !== $this->p2Uid) {
            throw new InvalidArgumentException($uid.' is not a player in this game');
        }

        $this->renew();

        if ($this->roundData[$this->round][$uid] !== null) {
            return false;
        }
        $this->roundData[$this->round][$uid] = $move;

        return true;
    }

    public function movesDone(): bool
    {
        return $this->roundData[$this->round][$this->p1Uid] instanceof RpsMove && $this->roundData[$this->round][$this->p2Uid] instanceof RpsMove;
    }

    public function executeRound(): ?string
    {

        $p1Move = $this->roundData[$this->round][$this->p1Uid];
        $p2Move = $this->roundData[$this->round][$this->p2Uid];

        $res = $this->calcWin($p1Move, $p2Move);
        $winner = null;
        switch ($res) {
            case 'draw':
                break;
            case 'p1':
                $this->p1Points++;
                $winner = $this->p1Uid;
                break;
            case 'p2':
                $this->p2Points++;
                $winner = $this->p2Uid;
                break;
            default:
                // fucking explode or smtn
                throw new Exception(self::class.'::calcWin() returned a non valid string (fix it)');
        }

        $this->newRound();

        return $winner;
    }

    public function checkWinner(): ?string
    {
        if ($this->p1Points >= self::WIN_CON) {
            return $this->p1Uid;
        }
        if ($this->p2Points >= self::WIN_CON) {
            return $this->p2Uid;
        }

        return null;
    }

    private function calcWin(RpsMove $p1Move, RpsMove $p2Move): string
    {
        if ($p1Move === $p2Move) {
            return 'draw';
        }
        $moveLogic = [
            RpsMove::ROCK,
            RpsMove::PAPER,
            RpsMove::SICSSORS,
            RpsMove::ROCK,
        ];
        $counter = count($moveLogic);

        for ($i = 0; $i < $counter; $i++) {
            if ($moveLogic[$i] !== $p1Move) {
                continue;
            }

            if ($p2Move === $moveLogic[$i + 1]) {
                return 'p2';
            }

            return 'p1';
        }

        return 'random_error_string'; // never here
    }

    private function newRound(): void
    {
        $this->round++;
        $this->roundData[$this->round] = [
            $this->p1Uid => null,
            $this->p2Uid => null,
        ];
    }
}
