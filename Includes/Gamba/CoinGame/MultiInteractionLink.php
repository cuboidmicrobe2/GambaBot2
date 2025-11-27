<?php

declare(strict_types=1);

namespace Gamba\CoinGame;

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\ApplicationCommand;
use Gamba\CoinGame\Tools\Players\Player;
use InvalidArgumentException;

/**
 * Link multiple interactions to a single game
 */
trait MultiInteractionLink
{
    private readonly string $host;

    private readonly string $linkId;

    /**
     * @var array<string, PlayerLink>
     */
    private array $links = [];

    final public function joinMultiInteractionLink(ApplicationCommand $interaction, Player $player): void
    {
        $this->links[$player->uid] = new PlayerLink($interaction, $player);
    }

    /**
     * Update the original response of all linked interactions.
     */
    final public function updateAllLinked(MessageBuilder $message): void
    {
        foreach ($this->links as $link) {
            $link->interaction->updateOriginalResponse($message);
        }
    }

    /**
     * @throws InvalidArgumentException If interaction is not linked.
     */
    final public function updateInteractionById(string $interactionId, MessageBuilder $message): void
    {
        $this->isValidId($interactionId);

        $this->links[$interactionId]->interaction->updateOriginalResponse($message);
    }

    /**
     * @throws InvalidArgumentException If interaction is not linked.
     */
    final public function getInteractionLink(string $interactionId): PlayerLink
    {
        $this->isValidId($interactionId);

        return $this->links[$interactionId];
    }

    protected function createLink(ApplicationCommand $hostInteraction, Player $hostPlayer): void
    {
        $this->linkId = $hostInteraction->id.'\\'.hrtime(true);

        $this->links[$hostInteraction->id] = $hostPlayer;
    }

    /**
     * @throws InvalidArgumentException If interaction is not linked.
     */
    private function isValidId(string $id): void
    {
        if (! isset($this->links[$id])) {
            throw new InvalidArgumentException($id.' is not linked to this game');
        }
    }
}
