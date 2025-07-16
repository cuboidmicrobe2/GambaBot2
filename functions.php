<?php

declare(strict_types = 1);

namespace GambaBot;

use Debug\CMD_FONT_COLOR;
use Debug\CMDOutput;
use Discord\Parts\Interactions\Interaction;

function getUserId(Interaction $interaction) : string {
    return $interaction->member->user->id ?? $interaction->user->id;
}

function getOptionValue(string $offset, Interaction $interaction) : mixed {
    if($interaction->data->options->offsetExists($offset)) return $interaction->data->options->offsetGet($offset)->value;
    return null;
}

function isImplementing(object $object, string $interface) : bool {
    return in_array($interface, class_implements($object));
}


function getCommandStrings(Interaction $interaction) : ?array {
    if(file_exists(__DIR__.'/Commands/content/strings.json')) {
        $strings = json_decode(file_get_contents(__DIR__.'/Commands/content/strings.json'), true);
        
        return array_key_exists($interaction->data->name, $strings) ? $strings[$interaction->data->name] : null;
    }
    echo CMDOutput::new()->add('strings.json is missing from ' . __DIR__. '/content', CMD_FONT_COLOR::YELLOW), PHP_EOL;

    return null;
}