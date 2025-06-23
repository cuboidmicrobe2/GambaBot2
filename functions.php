<?php

namespace GambaBot;

use Discord\Parts\Interactions\Interaction;

function getUserId(Interaction $interaction) : string {
    return isset($interaction->member) ? $interaction->member->user->id : $interaction->user->id;
}