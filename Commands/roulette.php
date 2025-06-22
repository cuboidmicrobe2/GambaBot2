<?php

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

global $discord, $gamba;

$discord->listenCommand('roulette', function(Interaction $interaction) use ($gamba) {
    $message = MessageBuilder::new()->setContent('Something went wrong');

    $gamba->roulette(
        uid:        $interaction->member->user->id,
        wager:      $interaction->data->options->offsetGet('amount')->value,
        bet:        $interaction->data->options->offsetGet('color')->value,
        message:    $message
    );

    $interaction->respondWithMessage($message);
});