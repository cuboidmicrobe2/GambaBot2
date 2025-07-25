<?php

declare(strict_types=1);

use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction;

global $discord, $gamba;

$discord->listenCommand('leaderboard', function (Interaction $interaction) use ($gamba, $discord): void {
    $interaction->acknowledgeWithResponse()->then(function () use ($interaction, $gamba, $discord): void {
        $leaderboard = $gamba->inventoryManager->leaderboard(10);

        $text = '';
        $counter = count($leaderboard);
        for ($i = 0; $i < $counter; $i++) {
            $text .= $leaderboard[$i]['user'].' - '.$leaderboard[$i]['coins'].PHP_EOL;
        }

        $interaction->updateOriginalResponse(MessageBuilder::new()->addEmbed(new Embed($discord)
            ->setTitle('Leaderboard')
            ->setDescription($text)
            ->setColor(EMBED_COLOR_PINK)
        ));
    });
});
