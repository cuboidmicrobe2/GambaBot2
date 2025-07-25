<?php

declare(strict_types=1);

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

use function GambaBot\Interaction\getUserId;

global $discord, $gamba;

$discord->listenCommand('daily', function (Interaction $interaction) use ($gamba): void {
    $message = MessageBuilder::new()->setContent('Something went wrong');

    $gamba->daily(
        uid: getUserId($interaction),
        message: $message
    );

    $interaction->respondWithMessage($message);
});
