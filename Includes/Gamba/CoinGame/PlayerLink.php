<?php

declare(strict_types=1);

namespace Gamba\CoinGame;

use Discord\Parts\Interactions\ApplicationCommand;
use Gamba\CoinGame\Tools\Players\Player;


/**
 * Links a player to an Interaction.
 */
final readonly class PlayerLink
{
    public function __construct(
        public ApplicationCommand $interaction,
        public Player $player
    ) {}
}
