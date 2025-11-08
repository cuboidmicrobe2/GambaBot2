<?php

declare(strict_types=1);

namespace Gamba\CoinGame;

use Discord\Parts\Interactions\ApplicationCommand;
use Gamba\CoinGame\Tools\Players\Player;

final class PlayerLink
{
    public function __construct(
        public readonly ApplicationCommand $interaction, 
        public readonly Player $player
    ) {}
}