<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Games\RPS;

use Discord\Discord;
use Exception;
use Gamba\CoinGame\GameInstance;
use Gamba\CoinGame\Tools\Players\Player;
use Gamba\CoinGame\Tools\Players\PlayerManager;
use Gamba\Loot\Item\InventoryManager;

final class RockPaperScissors extends GameInstance
{
    use PlayerManager;

    private const int WIN_CON = 3;

    public private(set) int $round = 0;

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

    public function __construct(
        Discord $discord,
        InventoryManager $inventoryManager,
        private readonly string $p1Uid,
        private readonly string $p2Uid,
        public private(set) int $bet
    ) {

        $p1 = new Player($p1Uid, $inventoryManager, $discord);
        $p2 = new Player($p2Uid, $inventoryManager, $discord);
        $p1->data->points = 0;
        $p1->data->moves = [];
        $p2->data->points = 0;
        $p2->data->moves = [];

        $this->addPlayers($p1, $p2);

        parent::__construct();
        $this->newRound();
    }

    public function __destruct()
    {
        $p1 = $this->getPlayerById($this->p1Uid);
        $p2 = $this->getPlayerById($this->p2Uid);
        if ($this->started) {
            if ($p1->data->points >= self::WIN_CON) {
                $p1->inventory->addCoins($this->bet * 2);

                return;
            }
            if ($p2->data->points >= self::WIN_CON) {
                $p2->inventory->addCoins($this->bet * 2);

                return;
            }
            if ($p1->data->moves[$this->round] !== null && $p2->data->moves[$this->round] === null) {
                $p1->inventory->addCoins($this->bet * 2);

                return;
            }
            if ($p2->data->moves[$this->round] !== null && $p1->data->moves[$this->round] === null) {
                $p2->inventory->addCoins($this->bet * 2);

                return;
            }

            return; // game has started but no one has made a move this round so both lose :)
        }
        $p1->inventory->addCoins($this->bet);
        $p2->inventory->addCoins($this->bet);
    }

    public function makeMove(string $uid, RpsMove $move): bool
    {
        $player = $this->getPlayerById($uid);

        $this->renew();

        if ($player->data->move !== null) {
            return false;
        }
        $player->data->move = $move;
        $player->data->moves[$this->round] = $move;

        return true;
    }

    public function movesDone(): bool
    {
        return $this->getPlayerById($this->p1Uid)->data->move instanceof RpsMove && $this->getPlayerById($this->p2Uid)->data->move instanceof RpsMove;
    }

    public function executeRound(): ?string
    {
        $p1Move = $this->getPlayerById($this->p1Uid)->data->move;
        $p2Move = $this->getPlayerById($this->p2Uid)->data->move;

        $res = $this->calcWin($p1Move, $p2Move);
        $winner = null;
        switch ($res) {
            case 'draw':
                break;
            case 'p1':
                $this->getPlayerById($this->p1Uid)->data->points++;
                $winner = $this->p1Uid;
                break;
            case 'p2':
                $this->getPlayerById($this->p2Uid)->data->points++;
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
        if ($this->getPlayerById($this->p1Uid)->data->points >= self::WIN_CON) {
            return $this->p1Uid;
        }
        if ($this->getPlayerById($this->p2Uid)->data->points >= self::WIN_CON) {
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
        $this->getPlayerById($this->p1Uid)->data->move = null;
        $this->getPlayerById($this->p2Uid)->data->move = null;
    }
}
