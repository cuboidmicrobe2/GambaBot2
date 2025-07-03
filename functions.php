<?php

namespace GambaBot;

use Discord\Parts\Interactions\Interaction;

function getUserId(Interaction $interaction) : string {
    return $interaction->member->user->id ?? $interaction->user->id;
}

function getOptionValue(string $offset, Interaction $interaction) : ?string {
    if($interaction->data->options->offsetExists($offset)) return $interaction->data->options->offsetGet($offset)->value;
    return null;
}

function isImplementing(object $object, string $interface) : bool {
    return in_array($interface, class_implements($object));
}