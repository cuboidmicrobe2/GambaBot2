<?php

declare(strict_types=1);

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\ApplicationCommand;
use Tools\Discord\Text\Format;

use function GambaBot\Discord\mention;
use function GambaBot\Interaction\getOptionValue;
use function GambaBot\Interaction\getUserId;

global $discord, $gamba;

$discord->listenCommand('pay', function (ApplicationCommand $interaction) use ($gamba): void {

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

    $receiverMention = Format::mention()->user($receiverId);
    $interaction->respondWithMessage(MessageBuilder::new()->setContent($receiverMention.' was paid `'.$payAmount.'` coins'));
});
