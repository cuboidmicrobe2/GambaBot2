<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\Players;

use ArrayIterator;
use Generator;
use LogicException;

trait PlayerManager
{   
    /**
     * @var array<int, Player>
     */
    private array $players = [];

    private int $playerIterator = 0;
    
    final public function addPlayers(Player ...$newPlayers): void
    {
        foreach ($newPlayers as $newPlayer) {
            foreach ($this->players as $player) {
                if ($newPlayer->uid === $player->uid) {
                    throw new LogicException($newPlayer->uid.' is already in this game');
                }
            }

            $this->players[$newPlayer->uid] = $newPlayer;
        }
    }

    // final public function goToNextPlayer(): void
    // {
    //     $this->playerIterator++;
    //     if (! isset($this->players[$this->playerIterator])) {
    //         $this->playerIterator = 0;
    //     }
    // }

    // final public function &getPlayer(): Player
    // {
    //     return $this->players[$this->playerIterator];
    // }

    final public function &getPlayerById(string $uid): Player
    {
        $player = $this->players[$uid] ?? null;

        if (! $player instanceof Player) {
            throw new LogicException($uid.' is not part of this game');
        }

        return $player;
    }

    /**
     * @return Player[]
     */
    final public function playerArray(): array
    {
        return $this->players;
    }
}