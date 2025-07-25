<?php

declare(strict_types=1);

use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction;

use function GambaBot\Discord\TextStyle\code;
use function GambaBot\Interaction\getUserId;

global $discord, $gamba;

$discord->listenCommand('stats', function (Interaction $interaction) use ($gamba, $discord) {
    $stats = $gamba->getUserStats(getUserId($interaction));
    $goldMaxPity = GOLD_PITY_CAP;
    $purpleMaxPity = PURPLE_PITY_CAP;

    $coins = code($stats['coins'] ?? '$error');
    $goldPity = code($stats['goldPity'] ?? '$error');
    $purplePity = code($stats['purplePity'] ?? '$error');

    $interaction->respondWithMessage(MessageBuilder::new()->addEmbed(new Embed($discord)
        ->setTitle('Stats')
        ->setDescription(<<<DESC
        **Coins** {$coins}
        **Gold Pity** {$goldPity} out of {$goldMaxPity}
        **Purple Pity** {$purplePity} out of {$purpleMaxPity}
        DESC)
        // ->addFieldValues('Coins', $stats['coins'] ?? 'error', true)
        // ->addFieldValues('Gold Pity', $stats['goldPity'] ?? 'error', true)
        // ->addFieldValues('Purple Pity', $stats['purplePity'] ?? 'error', true)
        ->setColor(EMBED_COLOR_PINK)
    ), true);
});
