<?php

declare(strict_types=1);

use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\ApplicationCommand;
use Discord\Parts\Interactions\MessageComponent;
use Gamba\CoinGame\GameData;
use Gamba\CoinGame\Games\BlackJack\BlackJack;
use Gamba\CoinGame\Games\BlackJack\HandResult;
use Gamba\CoinGame\Tools\Components\Factories\ButtonFactory;
use Gamba\CoinGame\Tools\Players\Player;
use Tools\Discord\Text\Format;

use function GambaBot\Interaction\buttonPressedByOwner;
use function GambaBot\Interaction\getOptionValue;
use function GambaBot\Interaction\getUserId;
use function GambaBot\Interaction\permissionToRun;

global $discord, $gamba;

$discord->listenCommand('blackjack', function (ApplicationCommand $interaction) use ($discord, $gamba): void {

    if (! permissionToRun($interaction)) {
        return;
    }

    $bet = (int) getOptionValue('bet', $interaction);
    $uid = getUserId($interaction);

    $inventory = $gamba->inventoryManager->getInventory($uid);
    $coins = $inventory->getCoins();

    if ($bet > $coins) {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('You do not have enough for a '.$bet.' coin bet! ('.$coins.' coins)'), ephemeral: true);

        return;
    }

    $game = new BlackJack($bet, decks: 2, player: new Player($uid, $gamba->inventoryManager, $discord));
    $buttonFactory = new ButtonFactory($interaction);
    $canSplit = $game->splitCheck();

    $canPickCard = $game->getCurrentHand()->playable;

    $gameLogic = function (string $action) use ($gamba, $interaction, $discord): void {

        /** @var BlackJack */
        $game = $gamba->games->getGame($interaction->id);
        $gameData = $gamba->games->getGameData($game);

        switch ($action) {
            case 'hit':
                $game->hit();
                break;
            case 'stand':
                $game->stand();
                break;
            case 'double':
                $coins = $game->player->inventory->getCoins();

                if ($coins < $game->bet) {
                    $interaction->sendFollowUpMessage(MessageBuilder::new()->setContent(Format::italic('Insufficent coins for this action, try another button!')), ephemeral: true);
                    break;
                }

                $game->double();
                $game->player->inventory->setCoins($coins - $game->bet);
                break;
            case 'split':
                $coins = $game->player->inventory->getCoins();

                if ($coins < $game->bet) {
                    $interaction->sendFollowUpMessage(MessageBuilder::new()->setContent(Format::italic('Insufficent coins for this action, try another button!')), ephemeral: true);
                    break;
                }

                $game->split();
                $game->player->inventory->setCoins($coins - $game->bet);
                break;
            default:
                throw new LogicException($action.' is not a valid action');
        }

        $otherHandsString = '';
        if ($otherHands = $game->showOtherPlayerHands()) {

            foreach ($otherHands as $handString) {
                $otherHandsString .= '> '.$handString.PHP_EOL;
            }
        }

        $dealer = $game->showDealerHand();
        $player = $game->showPlayerHand();

        $disableCardPickup = ! $game->getCurrentHand()->playable;

        $gameData->setButtonDisabledStateArray([
            'hit' => $disableCardPickup,
            'double' => $disableCardPickup,
            'split' => ! $game->splitCheck(),
        ]);

        $dealer = $game->showDealerHand();
        $player = $game->showPlayerHand();

        $otherHands ??= '';

        $embed = new Embed($discord)
            ->setTitle($dealer)
            ->setDescription($player.PHP_EOL.$otherHandsString)
            ->setFooter('Starting bet: '.$game->bet)
            ->setColor(EMBED_COLOR_PINK);

        if (! $game->playableHands()) {

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

            $game->player->inventory->setCoins(
                $game->player->inventory->getCoins() + $returnCoins
            );

            $interaction->updateOriginalResponse(MessageBuilder::new()->addEmbed($embed));

            return;
        }

        $interaction->updateOriginalResponse(MessageBuilder::new()->addComponent($gamba->games->getNewActionRow($game))->addEmbed($embed));
    };

    $buttonFactory->create(Button::STYLE_SECONDARY, 'hit')->setLabel('Hit')->setDisabled(! $canPickCard)->setListener(function (MessageComponent $buttonInteraction) use ($gameLogic): void {
        if (! buttonPressedByOwner($buttonInteraction)) {
            return;
        }

        $gameLogic('hit');
    }, $discord);

    $buttonFactory->create(Button::STYLE_SECONDARY, 'stand')->setLabel('Stand')->setListener(function (MessageComponent $buttonInteraction) use ($gameLogic): void {
        if (! buttonPressedByOwner($buttonInteraction)) {
            return;
        }

        $gameLogic('stand');
    }, $discord);

    $buttonFactory->create(Button::STYLE_SECONDARY, 'double')->setLabel('Double')->setDisabled(! $canPickCard)->setListener(function (MessageComponent $buttonInteraction) use ($gameLogic): void {
        if (! buttonPressedByOwner($buttonInteraction)) {
            return;
        }

        $gameLogic('double');
    }, $discord);

    $buttonFactory->create(Button::STYLE_SECONDARY, 'split')->setLabel('Split')->setDisabled((! $canSplit))->setListener(function (MessageComponent $buttonInteraction) use ($gameLogic): void {
        if (! buttonPressedByOwner($buttonInteraction)) {
            return;
        }

        $gameLogic('split');
    }, $discord);

    $gamba->games->addGame($game, GameData::create($interaction, $buttonFactory->createCollection(), $buttonFactory->getMap()));

    if ($game->dealerBlackJack()) {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent(Format::italic('Dealer has blackjack! (bet was refunded)')), ephemeral: true);
        $gamba->games->closeGame($game);

        return;
    }

    $inventory->setCoins($coins - $bet);

    $dealer = $game->showDealerHand();
    $player = $game->showPlayerHand();

    $interaction->respondWithMessage(MessageBuilder::new()->addComponent($buttonFactory->createActionRow())->addEmbed(new Embed($discord)
        ->setTitle($dealer)
        ->setDescription($player)
        ->setFooter('Starting bet: '.$game->bet)
        ->setColor(EMBED_COLOR_PINK)
    ));
});
