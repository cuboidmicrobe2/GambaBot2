<?php

declare(strict_types=1);

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\ApplicationCommand;

use function GambaBot\Interaction\getCommandStrings;

global $discord;

$discord->listenCommand('news', function (ApplicationCommand $interaction): void {

    $news = getCommandStrings($interaction);

    $interaction->respondWithMessage(MessageBuilder::new()->setContent($news?->content ?? 'No news'), ephemeral: true);
});
