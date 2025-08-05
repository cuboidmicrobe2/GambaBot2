<?php

declare(strict_types=1);

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction;
use Gamba\CoinGame\ButtonCollection;
use Gamba\CoinGame\ComponentIdCreator;
use Gamba\CoinGame\GameData;
use Gamba\CoinGame\Games\BlackJack\BlackJack;
use Gamba\CoinGame\Games\BlackJack\HandResult;
use Gamba\Loot\Item\Inventory;

use function GambaBot\Interaction\getOptionValue;
use function GambaBot\Discord\TextStyle\italic;
use function GambaBot\Interaction\buttonPressedByOwner;
use function GambaBot\Interaction\getUserId;

global $discord, $gamba;

$discord->listenCommand('blackjack', function (Interaction $interaction) use ($discord, $gamba): void {

    $bet = (int) getOptionValue('bet', $interaction);
    $uid = getUserId($interaction);

    $inventory = $gamba->inventoryManager->getInventory($uid);
    $coins = $inventory->getCoins();

    if ($bet > $coins) {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('You do not have enough for a '.$bet.' coin bet! ('.$coins.' coins)'), ephemeral: true);
        return;
    }

    $game = new BlackJack($bet, decks: 2);

    $idCreator = new ComponentIdCreator($interaction);

    /** @var ButtonCollection<int, Button> */
    $buttons = new ButtonCollection(4);

    $canSplit = $game->splitCheck();

    $gameLogic = function(string $action, Inventory $inventory) use ($gamba, $interaction, $uid, $discord) {

        /** @var BlackJack */
        $game = $gamba->games->getGame($interaction->id);
        $gameData = $gamba->games->getGameData($game);

        $inventory = $gamba->inventoryManager->getInventory($uid);

        switch ($action) {
            case 'hit';
                $game->hit();
                break;
            case 'stand':
                $game->stand();
                break;
            case 'double':
                $coins = $inventory->getCoins();
                
                if ($coins < $game->bet) {
                    $interaction->sendFollowUpMessage(MessageBuilder::new()->setContent(italic('Insufficent coins for this action, try another button')), ephemeral: true);
                    break;
                }

                $game->double();
                $inventory->setCoins($coins - $game->bet);
                break;
            case 'split':
                $coins = $inventory->getCoins();
                
                if ($coins < $game->bet) {
                    $interaction->sendFollowUpMessage(MessageBuilder::new()->setContent(italic('Insufficent coins for this action, try another button')), ephemeral: true);
                    break;
                }

                $game->split();
                $inventory->setCoins($coins - $game->bet);
                break;
            default:
                throw new LogicException($action.' is not a valid action');
        }

        if ($otherHands = $game->showOtherPlayerHands()) {
            // add other hands to embed
            $otherHandsString = '';
            foreach ($otherHands as $handString) {
                $otherHandsString .= $handString.PHP_EOL;
            }
        }

        $dealer = $game->showDealerHand();
        $player = $game->showPlayerHand();
        
        $canSplit = $game->splitCheck();

        $disableDouble = true;
        if ($inventory->getCoins() >= $game->bet) {
            $disableDouble = false;
        }

        $gameData->setButtonDisabledState('split', ! $canSplit);
        $gameData->setButtonDisabledState('double', $disableDouble);

        $buttonRow = $gamba->games->getNewActionRow($game);
        $dealer = $game->showDealerHand();
        $player = $game->showPlayerHand();

        $embed = new Embed($discord)
            ->setTitle($dealer)
            ->setDescription($player)
            ->setFooter('Starting bet: '.$game->bet)
            ->setColor(EMBED_COLOR_PINK);

        if (! $game->playableHands()) {

            echo PHP_EOL, 'NO MORE HANDS', PHP_EOL;

            $game->dealDealer();

            $result = $game->calcResult();
            $handStrings = $game->getAllPlayerHandStrings();

            $embed->setTitle($game->showDealerHand(redactFirst: false));

            $returnCoins = 0;
            $resultString = '';

            $resultCounter = count($result);
            for ($i = 0; $i < $resultCounter; $i++) {

                $handWin = match ($result[$i]) {
                    HandResult::WIN => $game->bet * 2,
                    HandResult::DOUBLE_WIN => ($game->bet * 2) * 2,
                    HandResult::TIE => $game->bet,
                    HandResult::LOSS => -$game->bet,
                    HandResult::DOUBLE_LOSS => -($game->bet * 2),
                    default => -99999,
                };

                if ($handWin > 0) {
                    $returnCoins += $handWin;
                }
                
                $resultString .= $handStrings[$i].' : '.$handWin.' coins'.PHP_EOL;
            }

            $embed->setDescription($resultString);

            $gamba->games->closeGame($game);

            $inventory->setCoins($inventory->getCoins() + $returnCoins);

            $interaction->updateOriginalResponse(MessageBuilder::new()->addEmbed($embed));

            return;
        }

        $interaction->updateOriginalResponse(MessageBuilder::new()->addComponent($buttonRow)->addEmbed($embed));
    };

    $hitButton = Button::primary($idCreator->createId('hit'))->setLabel('Hit')->setListener(function(Interaction $buttonInteraction) use ($gameLogic, $inventory) {
        if (! buttonPressedByOwner($buttonInteraction)) {
            return;
        }

        $gameLogic('hit', $inventory);
    }, $discord);

    $standButton = Button::primary($idCreator->createId('stand'))->setLabel('Stand')->setListener(function(Interaction $buttonInteraction) use ($gameLogic, $inventory) {
        if (! buttonPressedByOwner($buttonInteraction)) {
            return;
        }

        $gameLogic('stand', $inventory);
    }, $discord);

    $doubleButton = Button::primary($idCreator->createId('double'))->setLabel('Double')->setListener(function(Interaction $buttonInteraction) use ($gameLogic, $inventory) {
        if (! buttonPressedByOwner($buttonInteraction)) {
            return;
        }

        $gameLogic('double', $inventory);
    }, $discord);

    $splitButton = Button::primary($idCreator->createId('split'))->setLabel('Split')->setDisabled((! $canSplit))->setListener(function(Interaction $buttonInteraction) use ($gameLogic, $inventory) {
        if (! buttonPressedByOwner($buttonInteraction)) {
            return;
        }

        $gameLogic('split', $inventory);
    }, $discord);

    $buttons->insert($hitButton, $standButton, $doubleButton, $splitButton);
    $buttonRow = new ActionRow;

    foreach ($buttons as $button) {
        $buttonRow->addComponent($button);
    }

    $gamba->games->addGame($game, GameData::create($interaction, $buttons));

    if ($game->dealerBlackJack()) {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent(italic('Dealer has blackjack!  (bet was refunded)')), ephemeral: true);
        return;
    }

    $inventory->setCoins($coins - $bet);

    $dealer = $game->showDealerHand();
    $player = $game->showPlayerHand();

    $interaction->respondWithMessage(MessageBuilder::new()->addComponent($buttonRow)->addEmbed(new Embed($discord)
        ->setTitle($dealer)
        ->setDescription($player)
        ->setFooter('Starting bet: '.$game->bet)
        ->setColor(EMBED_COLOR_PINK)    
    )); 
});