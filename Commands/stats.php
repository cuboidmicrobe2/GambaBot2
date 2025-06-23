<?php

use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction;

global $discord, $gamba;


$discord->listenCommand('stats', function(Interaction $interaction) use ($gamba, $discord) {
    $stats = $gamba->getUserStats($interaction->member->user->id);

    $interaction->respondWithMessage(MessageBuilder::new()->addEmbed(new Embed($discord)
        ->setTitle('Stats')
        ->setDescription(<<<DESC
        **Coins** `{$stats['coins']}`
        **Gold Pity** `{$stats['goldPity']}`
        **Purple Pity** `{$stats['purplePity']}`
        DESC)
        // ->addFieldValues('Coins', $stats['coins'] ?? 'error', true)
        // ->addFieldValues('Gold Pity', $stats['goldPity'] ?? 'error', true)
        // ->addFieldValues('Purple Pity', $stats['purplePity'] ?? 'error', true)
        ->setColor(EMBED_COLOR_PINK)
    ), true);
});