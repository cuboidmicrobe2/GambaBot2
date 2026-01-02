<?php

declare(strict_types=1);

use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\ApplicationCommand;

use function GambaBot\Interaction\permissionToRun;

global $gatchaBot;

$gatchaBot->discord->listenCommand('leaderboard', function (ApplicationCommand $interaction) use ($gatchaBot): void {

    if (! permissionToRun($interaction)) {
        return;
    }

    $interaction->acknowledgeWithResponse()->then(function () use ($interaction, $gatchaBot): void {
        $leaderboard = $gatchaBot->gamba->inventoryManager->leaderboard(10);

        $text = '';
        $counter = count($leaderboard);
        for ($i = 0; $i < $counter; $i++) {
            $text .= $leaderboard[$i]['user'].' - '.$leaderboard[$i]['coins'].PHP_EOL;
        }

        $interaction->updateOriginalResponse(MessageBuilder::new()->addEmbed(new Embed($gatchaBot->discord)
            ->setTitle('Leaderboard')
            ->setDescription($text)
            ->setColor(EMBED_COLOR_PINK)
        ));
    });
});
