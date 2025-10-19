<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\Players;

use Generator;
use InvalidArgumentException;

/**
 * Manages multiple players on a single interaction
 */
trait PlayerManager
{   
    /**
     * @var array<int, Player>
     */
    private array $players = [];
    
    /**
     * @throws InvalidArgumentException Player not in game.
     */
    final public function addPlayers(Player ...$newPlayers): void
    {
        foreach ($newPlayers as $player) {
            if ($this->playerInGame($player)) {
                throw new InvalidArgumentException($player->uid.' is already in this game');
            }

            $this->players[$player->uid] = $player;
        }
    }

    /**
     * Remove a player from the game.
     * 
     * @param string|Player $player     Discord user id or a **Player** object
     * @throws InvalidArgumentException The player is not part of this game.
     */
    final public function removePlayer(string|Player $player): void
    {
        if (! $this->playerInGame($player)) {
            throw new InvalidArgumentException('The player is not part of this game');
        }

        $id = ($player instanceof Player) ? $player->uid : $player;

        unset($this->players[$id]);
    }

    /**
     * Get a **Player** object by its id.
     *
     * @param string $uid               Discord user id.
     * @return Player                   The player.
     * @throws InvalidArgumentException The player is not part of this game.
     */
    final public function getPlayerById(string $uid): Player
    {
        if (! $this->playerInGame($uid)) {
            throw new InvalidArgumentException($uid.' is not part of this game');
        }

        return $this->players[$uid];
    }

    /**
     * Get a list of all player
     * 
     * @return array<int, Player>
     */
    final public function playerArray(): array
    {
        return $this->players;
    }

    /**
     * Checks if a player is in the game.
     * 
     * @param string|Player $player     Discord user id or a **Player** object
     * @return bool                     Whether the player is in this game.
     */
    final public function playerInGame(string|Player $player): bool
    {
        if ($player instanceof Player) {
            $player = $player->uid;
        }

        return isset($this->players[$player]);
    }

    /**
     * Get the amount of players in the game.
     * 
     * @return int  Player count.
     */
    final public function playerCount(): int
    {
        return count($this->players);
    }

    final protected function createPlayerGenerator(): Generator
    {
        yield from $this->players;
    }
}