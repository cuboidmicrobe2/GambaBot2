<?php

declare(strict_types = 1);

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use Debug\CMD_FONT_COLOR;
use Debug\CMDOutput;

use function GambaBot\getCommandStrings;

global $discord;

$discord->listenCommand('news', function(Interaction $interaction) {

    $news = getCommandStrings($interaction)['content'] ?? 'No news found...';
    
    $interaction->respondWithMessage(MessageBuilder::new()->setContent($news), ephemeral: true);
});