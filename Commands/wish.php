<?php

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction;
use Gamba\Loot\Rarity;
use Gamba\Tools\ButtonCollectionManager;

use function GambaBot\getUserId;

define('WISH_PRICE', 1000);


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
    $uid = getUserId($interaction);
    $coins = $gamba->inventoryManager->getInventory($uid)->getcoins();
    $rolls = $interaction->data->options->offsetGet('amount')->value;
    if($coins < $rolls * WISH_PRICE) {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('You do not have enough coins for that! (`'.$coins.'` coins) use '.COMMAND_LINK_DAILY.' for free daily coins'));
        return;
    }

    $items = $gamba->wish(
        uid:    $uid,
        rolls:  $rolls
    );

    $embeds = [];
    foreach($items as $item) {
        $embeds[] = new Embed($discord)->setTitle($item->name)->setColor($item->rarity->getColor());
    }
    $interaction->respondWithMessage(MessageBuilder::new()->addEmbed(...$embeds));

//     $buttonId = (string)hrtime(true) . (string)mt_rand(100, 999);
//     $button = Button::new(Button::STYLE_PREMIUM)->setLabel('->')->setCustomId($buttonId)->setListener(function(Interaction $interaction) use ($wishButtons) {

//     }, $discord);
//     Button::new(Button::STYLE_DANGER, )
//     $row = ActionRow::new()->addComponent();
});