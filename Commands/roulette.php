<?php

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

use function GambaBot\getUserId;
use function GambaBot\getOptionValue;

global $discord, $gamba;

$discord->listenCommand('roulette', function(Interaction $interaction) use ($gamba) {
    $message = MessageBuilder::new()->setContent('Something went wrong');

    $gamba->roulette(
        uid:        getUserId($interaction),
        wager:      getOptionValue('amount', $interaction),
        bet:        getOptionValue('color', $interaction),
        message:    $message
    );

    $interaction->respondWithMessage($message);
});