<?php

declare(strict_types=1);

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

use function GambaBot\Discord\mention;
use function GambaBot\Interaction\getOptionValue;
use function GambaBot\Interaction\getUserId;

global $discord, $gamba;

$discord->listenCommand('pay', function (Interaction $interaction) use ($gamba): void {

    $payAmount = getOptionValue('amount', $interaction);

    $authorId = getUserId($interaction);
    $authorInventory = $gamba->inventoryManager->getInventory($authorId);
    $authorCoins = $authorInventory->getCoins();

    if ($authorCoins < $payAmount) {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('You do not have enough to send `'.$payAmount.'` coins (`'.$authorCoins.'` in bank)'), true);

        return;
    }

    $receiverId = getOptionValue('to', $interaction);

    if ($receiverId === $authorId) {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('Are you fucking stupid?'), true);

        return;
    }

    $receiverInventory = $gamba->inventoryManager->getInventory($receiverId);

    $authorInventory->setCoins($authorCoins - $payAmount);
    $receiverInventory->setCoins($receiverInventory->getCoins() + $payAmount);

    $interaction->respondWithMessage(MessageBuilder::new()->setContent(mention($receiverId).' was paid `'.$payAmount.'` coins'));
});
