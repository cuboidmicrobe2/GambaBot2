<?php

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use Gamba\CoinGame\Roulette\Color;
use Gamba\CoinGame\Roulette\Roulette;

global $discord, $inventoryManager;

$discord->listenCommand('roulette', function(Interaction $interaction) use ($inventoryManager) {
    $userInventory = $inventoryManager->getInventory($interaction->member->user->id);
    $wager = $interaction->data->options->offsetGet('amount')->value;
    $bet = $interaction->data->options->offsetGet('color')->value;
    $coins = $userInventory->getCoins();

    if($coins < $wager) {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('You do not have enough coins for that! ('.$coins.' coins)'), true);
        return;
    }
    
    $color = Color::getFromRoll(Roulette::roll());

    if($color->isMatch($bet)) {
        $winAmount = match($color) {
            Color::GREEN => $wager * 13,
            default => $wager * 2
        };

        $userInventory->setCoins($coins + $winAmount);

        $interaction->respondWithMessage(MessageBuilder::new()->setContent('You rolled ' . $color->name . ' and won ' . $winAmount . ' coins!'));
    }
    else {
        $userInventory->setCoins($coins - $wager);

        $interaction->respondWithMessage(MessageBuilder::new()->setContent('You rolled ' . $color->name . ' and lost ' . $wager . ' coins!'));
    }
});