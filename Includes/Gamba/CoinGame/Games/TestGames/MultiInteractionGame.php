<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Games\TestGames;

use Discord\Parts\Interactions\ApplicationCommand;
use Gamba\CoinGame\Attributes\LogOnClose;
use Gamba\CoinGame\GameInstance;
use Gamba\CoinGame\MultiInteractionLink;
use Gamba\CoinGame\Tools\Players\Player;

#[LogOnClose(PHP_EOL.'A MultiInteractionGame was closed'.PHP_EOL)]
final class MultiInteractionGame extends GameInstance
{
    use MultiInteractionLink;

    public function __construct(ApplicationCommand $interaction, Player $host)
    {
        parent::__construct();
        $this->createLink($interaction, $host);
    }
}
