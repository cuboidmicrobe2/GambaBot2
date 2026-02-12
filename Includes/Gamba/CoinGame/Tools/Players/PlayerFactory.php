<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\Players;

use Gamba\CoinGame\Tools\Players\Player;
use Gamba\Loot\Item\InventoryManager;

final class PlayerFactory
{

    public function __construct(private InventoryManager $inventoryManager) {}
    
    /**
     * Create a new player object.
     *
     * @param string $DiscordId Discord id of the player.
     * @return Player Reference to the new player.
     * 
     * @throws InvalidArgumentException If user is a bot.
     */
    public function createPlayer(string $DiscordId): Player
    {
        return new Player($DiscordId, $this->inventoryManager);
    }
}