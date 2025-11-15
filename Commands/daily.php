<?php

declare(strict_types=1);

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\ApplicationCommand;

use function GambaBot\Interaction\getUserId;
use function GambaBot\Interaction\permissionToRun;

global $discord, $gamba;

$discord->listenCommand('daily', function (ApplicationCommand $interaction) use ($gamba): void {

    if (! permissionToRun($interaction)) {
        return;
    }

    $message = MessageBuilder::new()->setContent('Something went wrong');

    $gamba->daily(
        uid: getUserId($interaction),
        message: $message
    );

    $interaction->respondWithMessage($message);
});
