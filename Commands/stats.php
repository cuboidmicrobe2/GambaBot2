<?php

declare(strict_types=1);

use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\ApplicationCommand;
use Tools\Discord\Text\Format;

use function GambaBot\Interaction\getUserId;
use function GambaBot\Interaction\permissionToRun;

global $discord, $gamba;

$discord->listenCommand('stats', function (ApplicationCommand $interaction) use ($gamba, $discord): void {
    if (! permissionToRun($interaction)) {
        return;
    }

    $stats = $gamba->getUserStats(getUserId($interaction));
    $goldMaxPity = GOLD_PITY_CAP;
    $purpleMaxPity = PURPLE_PITY_CAP;

    $coins = Format::code((string) $stats['coins'] ?? '$error');
    $goldPity = Format::code((string) $stats['goldPity'] ?? '$error');
    $purplePity = Format::code((string) $stats['purplePity'] ?? '$error');

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
