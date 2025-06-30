<?php

namespace GambaBot;

use Discord\Parts\Interactions\Interaction;

function getUserId(Interaction $interaction) : string {
    return $interaction->member->user->id ?? $interaction->user->id;
}