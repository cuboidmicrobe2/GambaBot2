<?php

declare(strict_types=1);

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Guild\Emoji;
use Discord\Parts\Interactions\ApplicationCommand;
use Discord\Parts\Interactions\MessageComponent;
use Gamba\CoinGame\GameData;
use Gamba\CoinGame\Games\RPS\RockPaperScissors;
use Gamba\CoinGame\Games\RPS\RpsMove;
use Gamba\CoinGame\Tools\Components\Factories\ButtonFactory;
use Tools\Discord\Text\Format;

use function GambaBot\Interaction\buttonPressedByOwner;
use function GambaBot\Interaction\buttonPressedByUser;
use function GambaBot\Interaction\buttonPresserId;
use function GambaBot\Interaction\getOptionValue;
use function GambaBot\Interaction\getUserId;
use function GambaBot\Interaction\permissionToRun;


global $discord, $gamba;

$discord->listenCommand('rps', function (ApplicationCommand $interaction) use ($discord, $gamba): void {
    if (! permissionToRun($interaction)) {
        return;
    }
    
    $p1 = getUserId($interaction);
    $p2 = getOptionValue('opponent', $interaction);

    if ($p1 === $p2) {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent(Format::italic('Do you not have any friends?')), ephemeral: true);

        return;
    }

    $bet = getOptionValue('bet', $interaction);

    $p1Inv = $gamba->inventoryManager->getInventory($p1);
    $p2Inv = $gamba->inventoryManager->getInventory($p2);

    if ($p1Inv->getCoins() < $bet || $p2Inv->getCoins() < $bet) {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('One or two players does not have enough coins to play'), ephemeral: true);

        return;
    }
    $game = new RockPaperScissors($discord, $gamba->inventoryManager, $p1, $p2, $bet);
    $startGameOptions = new ActionRow;
    $buttonFactory = new ButtonFactory($interaction);

    $gameLogic = function (RpsMove $move, MessageComponent $buttonInteraction) use ($interaction, $gamba, $discord, $p1, $p2): void {
        $player = buttonPresserId($buttonInteraction);

        /**
         * @var ?RockPaperScissors
         */
        $game = $gamba->games->getGame($interaction->id);

        $player1 = $game->getPlayerById($p1);
        $player2 = $game->getPlayerById($p2);

        if ($game->makeMove($player, $move)) {

            if ($game->movesDone()) {

                $p1Move = $player1->data->move;
                $p2Move = $player2->data->move;

                $game->executeRound();

                if ($winner = $game->checkWinner()) {

                    $interaction->updateOriginalResponse(MessageBuilder::new()->addEmbed(new Embed($discord)
                        ->setTitle($player1->name.' '.$p1Move?->getEmoji().' ['.Format::code($player1->data->points.' - '.$player2->data->points).'] '.$p2Move?->getEmoji().' '.$player2->name)
                        ->setDescription('Round: '.Format::code((string) ($game->round - 1)))
                        ->setColor(EMBED_COLOR_PINK)
                    ));

                    $winnerUsername = $game->getPlayerById($winner)->name;

                    $scoreFormatted = '$score';
                    if ($player1->data->points > $player2->data->points) {
                        $scoreFormatted = Format::code($player1->data->points.' - '.$player2->data->points);
                    } else {
                        $scoreFormatted = Format::code($player2->data->points.' - '.$player1->data->points);
                    }

                    $p1MoveHistory = '';
                    $p2MoveHistory = '';

                    // Round history is indexed by round number (starting on 1)
                    $counter = count($player1->data->moves) + 1;
                    for ($i = 1; $i < $counter; $i++) {
                        $p1MoveHistory .= $player1->data->moves[$i]?->getEmoji().' ';
                        $p2MoveHistory .= $player2->data->moves[$i]?->getEmoji().' ';
                    }

                    $interaction->sendFollowUpMessage(MessageBuilder::new()->addEmbed(new Embed($discord)
                        ->setTitle($winnerUsername.' winns '.$scoreFormatted)
                        ->setDescription('Coins: '.Format::code((string) $game->bet))
                        ->setColor(EMBED_COLOR_GREEN)
                        ->addFieldValues(
                            'Players',
                            <<<PLAYERS
                            {$player1->name}
                            {$player2->name}
                            PLAYERS,
                            inline: true
                        )
                        ->addFieldValues(
                            'Moves',
                            <<<MOVES
                            {$p1MoveHistory}
                            {$p2MoveHistory}
                            MOVES,
                            inline: true
                        )
                    ));

                    $gamba->games->closeGame($game);

                    return;
                }
                $interaction->updateOriginalResponse(MessageBuilder::new()->addEmbed(new Embed($discord)
                    ->setTitle(Format::italic($player1->name).' '.$p1Move?->getEmoji().' ['.Format::code($player1->data->points.' - '.$player2->data->points).'] '.$p2Move?->getEmoji().' '.Format::italic($player2->name))
                    ->setDescription('Round: '.Format::code((string) $game->round))
                    ->setColor(EMBED_COLOR_PINK)
                ));

                return;
            }

            $p1Move = '';
            $p2Move = '';

            if ($game->round > 1) {
                $p1Move = ' '.$player1->data->moves[$game->round - 1]->getEmoji();
                $p2Move = $player2->data->moves[$game->round - 1]->getEmoji().' ';
            }

            $p1NameStyled = ($player1->data->move === null) ? Format::italic($player1->name) : $player1->name;
            $p2NameStyled = ($player2->data->move === null) ? Format::italic($player2->name) : $player2->name;
            $interaction->updateOriginalResponse(MessageBuilder::new()->addEmbed(new Embed($discord)
                ->setTitle($p1NameStyled.$p1Move.' ['.Format::code($player1->data->points.' - '.$player2->data->points).'] '.$p2Move.$p2NameStyled)
                ->setDescription('Round: '.Format::code((string) $game->round))
                ->setColor(EMBED_COLOR_PINK)
            ));
        }
    };

    $buttonStart = $buttonFactory->create(Button::STYLE_SUCCESS, 'accept')->setLabel('Accept')->setListener(function (MessageComponent $buttonInteraction) use ($p1, $p2, $discord, $gamba, $interaction, $bet): void {
        if (! buttonPressedByUser($p2, $buttonInteraction)) {
            return;
        }

        /**
         * @var ?RockPaperScissors
         */
        $game = $gamba->games->getGame($interaction->id);
        $player1 = $game->getPlayerById($p1);
        $player2 = $game->getPlayerById($p2);

        $p1Coins = $player1->inventory->getCoins();
        $p2Coins = $player2->inventory->getCoins();

        if ($p1Coins < $bet || $p2Coins < $bet) {
            $interaction->updateOriginalResponse(MessageBuilder::new()->setContent('A player no longer has enough coins to play'));
            $gamba->games->closeGame($game);

            return;
        }

        $player1->inventory->setCoins($p1Coins);
        $player2->inventory->setCoins($p2Coins);

        $gameData = $gamba->games->getGameData($game);
        $gameData->removeButton('decline');
        $gameData->removeButton('accept');
        $gameOptions = $gamba->games->getNewActionRow($game);

        $game->renew();
        $game->started = true;

        $interaction->updateOriginalResponse(MessageBuilder::new()->setContent('')->addComponent($gameOptions)->addEmbed(new Embed($discord)
            ->setTitle(Format::italic($player1->name).' ['.Format::code('0 - 0').'] '.Format::italic($player2->name))
            ->setDescription('Round: '.Format::code((string) $game->round))
            ->setColor(EMBED_COLOR_PINK)
        ));
    }, $discord);

    $buttonDecline = $buttonFactory->create(Button::STYLE_SUCCESS, 'accept')->setLabel('Decline')->setListener(function (MessageComponent $buttonInteraction) use ($p2, $gamba, $interaction): void {
        if (! buttonPressedByUser($p2, $buttonInteraction)) {
            return;
        }
        $game = $gamba->games->getGame($interaction->id);
        $gamba->games->closeGame($game);

        $interaction->updateOriginalResponse(MessageBuilder::new()->setContent(Format::italic('The match was declined')));
    }, $discord);

    $buttonFactory->create(Button::STYLE_SECONDARY, 'rock')->setEmoji(new Emoji($discord, ['id' => null, 'name' => '🪨']))->setLabel(' ')->setListener(function (MessageComponent $buttonInteraction) use ($p2, $gameLogic): void {
        if (! buttonPressedByOwner($buttonInteraction) && ! buttonPressedByUser($p2, $buttonInteraction)) {
            return;
        }
        $gameLogic(RpsMove::ROCK, $buttonInteraction);
    }, $discord);

    $buttonFactory->create(Button::STYLE_SECONDARY, 'paper')->setEmoji(new Emoji($discord, ['id' => null, 'name' => '📰']))->setLabel(' ')->setListener(function (MessageComponent $buttonInteraction) use ($p2, $gameLogic): void {
        if (! buttonPressedByOwner($buttonInteraction) && ! buttonPressedByUser($p2, $buttonInteraction)) {
            return;
        }
        $gameLogic(RpsMove::PAPER, $buttonInteraction);
    }, $discord);

    $buttonFactory->create(Button::STYLE_SECONDARY, 'scissors')->setEmoji(new Emoji($discord, ['id' => null, 'name' => '✂️']))->setLabel(' ')->setListener(function (MessageComponent $buttonInteraction) use ($p2, $gameLogic): void {
        if (! buttonPressedByOwner($buttonInteraction) && ! buttonPressedByUser($p2, $buttonInteraction)) {
            return;
        }
        $gameLogic(RpsMove::SICSSORS, $buttonInteraction);
    }, $discord);

    $gamba->games->addGame($game, GameData::create($interaction, $buttonFactory->createCollection(), $buttonFactory->getMap()));

    $startGameOptions->addComponent($buttonStart);
    $startGameOptions->addComponent($buttonDecline);

    $userMention = Format::mention()->user($p2);
    $interaction->respondWithMessage(MessageBuilder::new()->setContent($userMention.' has been challenged to play rock paper scissors for '.Format::code((string) $bet).' coins')->addComponent($startGameOptions));
});
