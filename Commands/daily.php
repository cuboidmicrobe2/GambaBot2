<?php

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

global $discord, $gamba;

$discord->listenCommand('daily', function(Interaction $interaction) use ($gamba) {
    $message = MessageBuilder::new()->setContent('Something went wrong');

    $gamba->daily(
        uid:        $interaction->member->user->id, 
        message:    $message
    );

    $interaction->respondWithMessage($message);
});