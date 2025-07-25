<?php

declare(strict_types=1);

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

use function GambaBot\Interaction\getOptionValue;
use function GambaBot\Interaction\getUserId;

global $discord, $gamba;

$discord->listenCommand('roulette', function (Interaction $interaction) use ($gamba): void {
    $message = MessageBuilder::new()->setContent('Something went wrong');

    $gamba->roulette(
        uid: getUserId($interaction),
        wager: getOptionValue('amount', $interaction),
        bet: getOptionValue('color', $interaction),
        message: $message
    );

    $interaction->respondWithMessage($message);
});
