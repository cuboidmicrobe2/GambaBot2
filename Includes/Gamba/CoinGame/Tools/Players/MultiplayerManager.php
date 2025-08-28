<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\Players;

use LogicException;

trait MultiplayerManager
{   
    /**
     * @var array<int, Player>
     */
    private array $players = [];

    private int $playeriterator = 0;
    
    final public function addPlayer(Player $newPlayer): void
    {
        foreach ($this->players as $player) {
            if ($newPlayer->uid === $player->uid) {
                throw new LogicException($newPlayer->uid.' is already in this game');
            }
        }

        $this->players[] = $newPlayer;
    }

    final public function goToNextPlayer(): void
    {
        $this->playeriterator++;
        if (! isset($this->players[$this->playeriterator])) {
            $this->playeriterator = 0;
        }
    }

    final public function &getPlayer(): Player
    {
        return $this->players[$this->playeriterator];
    }
}