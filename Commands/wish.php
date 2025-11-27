<?php

declare(strict_types=1);

use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\ApplicationCommand;
use Gamba\Loot\Item\ItemCollection;

use function GambaBot\Interaction\getOptionValue;
use function GambaBot\Interaction\getUserId;
use function GambaBot\Interaction\permissionToRun;

global $discord, $gamba;

$discord->listenCommand('wish', function (ApplicationCommand $interaction) use ($gamba, $discord): void {
    if (! permissionToRun($interaction)) {
        return;
    }

    $message = MessageBuilder::new();

    $items = $gamba->wish(
        uid: getUserId($interaction),
        rolls: getOptionValue('amount', $interaction),
        // discord:    $discord,
        message: $message
    );

    if ($items instanceof ItemCollection) {
        $embeds = [];
        foreach ($items as $item) {
            $embeds[] = new Embed($discord)->setTitle($item->name)->setColor($item->rarity->getColor());
        }

        $message->setContent('')->addEmbed(...$embeds);
    }

    $interaction->respondWithMessage($message);
});
