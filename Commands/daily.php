<?php

declare(strict_types=1);

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\ApplicationCommand;

use function GambaBot\Interaction\getUserId;
use function GambaBot\Interaction\permissionToRun;

global $gachaBot;

$gachaBot->discord->listenCommand('daily', function (ApplicationCommand $interaction) use ($gachaBot): void {

    if (! permissionToRun($interaction)) {
        return;
    }

    $message = MessageBuilder::new()->setContent('Something went wrong');

    $gachaBot->gamba->daily(
        uid: getUserId($interaction),
        message: $message
    );

    $interaction->respondWithMessage($message);
});
