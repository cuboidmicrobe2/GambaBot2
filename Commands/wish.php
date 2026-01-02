<?php

declare(strict_types=1);

use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\ApplicationCommand;
use Gamba\Loot\Item\ItemCollection;

use function GambaBot\Interaction\getOptionValue;
use function GambaBot\Interaction\getUserId;
use function GambaBot\Interaction\permissionToRun;

global $gatchaBot;

$gatchaBot->discord->listenCommand('wish', function (ApplicationCommand $interaction) use ($gatchaBot): void {
    if (! permissionToRun($interaction)) {
        return;
    }

    $message = MessageBuilder::new();

    $items = $gatchaBot->gamba->wish(
        uid: getUserId($interaction),
        rolls: getOptionValue('amount', $interaction),
        // discord:    $discord,
        message: $message
    );

    if ($items instanceof ItemCollection) {
        $embeds = [];
        foreach ($items as $item) {
            $embeds[] = new Embed($gatchaBot->discord)->setTitle($item->name)->setColor($item->rarity->getColor());
        }

        $message->setContent('')->addEmbed(...$embeds);
    }

    $interaction->respondWithMessage($message);
});
