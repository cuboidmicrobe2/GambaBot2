<?php

declare(strict_types=1);

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\ApplicationCommand;

use function GambaBot\Interaction\getOptionValue;
use function GambaBot\Interaction\getUserId;
use function GambaBot\Interaction\permissionToRun;

global $gachaBot;

$gachaBot->discord->listenCommand('roulette', function (ApplicationCommand $interaction) use ($gachaBot): void {
    if (! permissionToRun($interaction)) {
        return;
    }

    $message = MessageBuilder::new()->setContent('Something went wrong');

    $gachaBot->gamba->roulette(
        uid: getUserId($interaction),
        wager: getOptionValue('amount', $interaction),
        bet: (int) getOptionValue('color', $interaction),
        message: $message
    );

    $interaction->respondWithMessage($message);
});
