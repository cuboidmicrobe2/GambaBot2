<?php

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction;
use Gamba\Loot\Rarity;
use Gamba\Tools\ButtonCollectionManager;

use function GambaBot\getUserId;

global $discord, $gamba;

// $wishButtons = new ButtonCollectionManager(60*5);

// function getColorFromRarity(Rarity $rarity) : string {
//     return match($rarity) {
//         Rarity::BLUE => '00A2E8',
//         Rarity::PURPLE => 'E767E8',
//         Rarity::GOLD => 'FFC90E',
//         default => 'FFFFFF'
//     };
// }

// move all inside $gamba::wish()
$discord->listenCommand('wish', function(Interaction $interaction) use ($gamba, $discord) {
    $interaction->respondWithMessage(MessageBuilder::new()->setContent('Working on this atm dont touch :)'), true);
    return;
    $message = MessageBuilder::new();
    
    $gamba->wish(
        uid:        getUserId($interaction),
        rolls:      $interaction->data->options->offsetGet('amount')->value,
        discord:    $discord,
        message:    $message
    );

    $interaction->respondWithMessage($message);

});