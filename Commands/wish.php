<?php

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction;
use Gamba\Loot\Item\ItemCollection;
use Gamba\Loot\Rarity;
use Gamba\Tools\ButtonCollectionManager;

use function GambaBot\getUserId;

global $discord, $gamba;

$discord->listenCommand('wish', function(Interaction $interaction) use ($gamba, $discord) {
    // $interaction->respondWithMessage(MessageBuilder::new()->setContent('Working on this atm dont touch :)'), true);
    // return;
    $message = MessageBuilder::new();
    
    $items = $gamba->wish(
        uid:        getUserId($interaction),
        rolls:      $interaction->data->options->offsetGet('amount')->value,
        //discord:    $discord,
        message:    $message
    );

    if($items instanceof ItemCollection) {
        $embeds = [];
        foreach($items as $item) {
            $embeds[] = new Embed($discord)->setTitle($item->name)->setColor($item->rarity->getColor());
        }

        $message->setContent('')->addEmbed(...$embeds);
    }
    else {
        $message?->setContent('You do not have enough coins for that! (`'.$coins.'` coins) use '.COMMAND_LINK_DAILY.' for free daily coins');
    }

    $interaction->respondWithMessage($message);
});