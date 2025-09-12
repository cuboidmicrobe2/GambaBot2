<?php

declare(strict_types=1);

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Guild\Emoji;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\User;
use Gamba\CoinGame\Tools\Components\ButtonCollection;
use Gamba\CoinGame\Tools\Components\ComponentIdCreator;
use Gamba\CoinGame\GameData;
use Gamba\CoinGame\Games\RPS\RockPaperScissors;
use Gamba\CoinGame\Games\RPS\RpsMove;
use Tools\Discord\Text\Format;

use function GambaBot\Interaction\buttonPressedByOwner;
use function GambaBot\Interaction\buttonPressedByUser;
use function GambaBot\Interaction\buttonPresserId;
use function GambaBot\Interaction\getOptionValue;
use function GambaBot\Interaction\getUserId;
use function GambaBot\Interaction\getUsername;

global $discord, $gamba;

$discord->listenCommand('rps', function (Interaction $interaction) use ($discord, $gamba): void {

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
    $buttons = new ButtonCollection(5);
    $startGameOptions = new ActionRow;
    $idCreator = new ComponentIdCreator($interaction);

    $gameLogic = function (RpsMove $move, Interaction $buttonInteraction) use ($interaction, $gamba, $discord, $p1, $p2): void {
        $player = buttonPresserId($buttonInteraction);

        /**
         * @var ?RockPaperScissors
         */
        $game = $gamba->games->getGame($interaction->id);

        $p1 = $game->getPlayerById($p1);
        $p2 = $game->getPlayerById($p2);

        if ($game->makeMove($player, $move)) {

            if ($game->movesDone()) {

                // $moves = $game->roundData[$game->round];
                $p1Move = $p1->data->move;
                $p2Move = $p2->data->move;

                $game->executeRound();

                if ($winner = $game->checkWinner()) {

                    $interaction->updateOriginalResponse(MessageBuilder::new()->addEmbed(new Embed($discord)
                        ->setTitle($p1->name.' '.$p1Move?->getEmoji().' ['.Format::code($p1->data->points.' - '.$p2->data->points).'] '.$p2Move?->getEmoji().' '.$p2->name)
                        ->setDescription('Round: '.Format::code((string) ($game->round - 1)))
                        ->setColor(EMBED_COLOR_PINK)
                    ));

                    $winnerUsername = $game->getPlayerById($winner)->name;
                    // $discord->users->fetch($winner)->then(function (User $user) use (&$winnerUsername): void {
                    //     $winnerUsername = getUsername($user);
                    // });

                    $scoreFormatted = '$score';
                    if ($p1->data->points > $p2->data->points) {
                        $scoreFormatted = Format::code($p1->data->points.' - '.$p2->data->points);
                    } else {
                        $scoreFormatted = Format::code($p2->data->points.' - '.$p1->data->points);
                    }

                    $p1MoveHistory = '';
                    $p2MoveHistory = '';

                    $counter = count($p1->data->moves);
                    for ($i = 0; $i < $counter; $i++) {
                        $p1MoveHistory .= $p1->data->moves[$i]?->getEmoji().' ';
                        $p2MoveHistory .= $p2->data->moves[$i]?->getEmoji().' ';
                    }

                    $interaction->sendFollowUpMessage(MessageBuilder::new()->addEmbed(new Embed($discord)
                        ->setTitle($winnerUsername.' winns '.$scoreFormatted)
                        ->setDescription('Coins: '.Format::code((string) $game->bet))
                        ->setColor(EMBED_COLOR_GREEN)
                        ->addFieldValues(
                            'Players',
                            <<<PLAYERS
                            {$p1->name}
                            {$p2->name}
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
                    ->setTitle(Format::italic($p1->name).' '.$p1Move?->getEmoji().' ['.Format::code($p1->data->points.' - '.$p2->data->points).'] '.$p2Move?->getEmoji().' '.Format::italic($p2->name))
                    ->setDescription('Round: '.Format::code((string) $game->round))
                    ->setColor(EMBED_COLOR_PINK)
                ));

                return;
            }

            $p1Move = '';
            $p2Move = '';

            if ($game->round > 1) {
                $p1Move = ' '.$p1->data->moves[$game->round - 1]->getEmoji();
                $p2Move = $p2->data->moves[$game->round - 1]->getEmoji().' ';
            }

            $p1NameStyled = ($p1->data->move === null) ? Format::italic($p1->name) : $p1->name;
            $p2NameStyled = ($p2->data->move === null) ? Format::italic($p2->name) : $p2->name;
            $interaction->updateOriginalResponse(MessageBuilder::new()->addEmbed(new Embed($discord)
                ->setTitle($p1NameStyled.$p1Move.' ['.Format::code($p1->data->points.' - '.$p2->data->points).'] '.$p2Move.$p2NameStyled)
                ->setDescription('Round: '.Format::code((string) $game->round))
                ->setColor(EMBED_COLOR_PINK)
            ));
        }
    };

    $p1Name = '$name';
    $p2Name = '$name';
    $discord->users->fetch($p1)->then(function (User $user) use (&$p1Name): void {
        $p1Name = getUsername($user);
    });
    $discord->users->fetch($p2)->then(function (User $user) use (&$p2Name): void {
        $p2Name = getUsername($user);
    });

    $buttonStart = Button::success($idCreator->createId('accept'))->setLabel('Accept')->setListener(function (Interaction $buttonInteraction) use ($p2, $p1Name, $p2Name, $discord, $gamba, $interaction, $idCreator, $p1Inv, $p2Inv, $bet): void {
        if (! buttonPressedByUser($p2, $buttonInteraction)) {
            return;
        }

        /**
         * @var ?RockPaperScissors
         */
        $game = $gamba->games->getGame($interaction->id);

        $p1Coins = $p1Inv->getCoins();
        $p2Coins = $p2Inv->getCoins();
        if ($p1Coins < $bet || $p2Coins < $bet) {
            $interaction->updateOriginalResponse(MessageBuilder::new()->setContent('A player no longer has enough coins to play'));
            $gamba->games->closeGame($game);

            return;
        }

        $p1Inv->setCoins($p1Coins - $bet);
        $p2Inv->setCoins($p2Coins - $bet);

        $gameData = $gamba->games->getGameData($game);
        $gameData->removeButton('decline');
        $gameData->removeButton('accept');
        $gameOptions = $gamba->games->getNewActionRow($game);

        $game->renew();
        $game->started = true;

        $interaction->updateOriginalResponse(MessageBuilder::new()->setContent('')->addComponent($gameOptions)->addEmbed(new Embed($discord)
            ->setTitle(Format::italic($p1Name).' ['.Format::code('0 - 0').'] '.Format::italic($p2Name))
            ->setDescription('Round: '.Format::code((string) $game->round))
            ->setColor(EMBED_COLOR_PINK)
        ));
    }, $discord);

    $buttonDecline = Button::danger($idCreator->createId('decline'))->setLabel('Decline')->setListener(function (Interaction $buttonInteraction) use ($p2, $gamba, $interaction): void {
        if (! buttonPressedByUser($p2, $buttonInteraction)) {
            return;
        }
        $game = $gamba->games->getGame($interaction->id);
        $gamba->games->closeGame($game);

        $interaction->updateOriginalResponse(MessageBuilder::new()->setContent(Format::italic('The match was declined')));
    }, $discord);

    $buttonRock = Button::secondary($idCreator->createId('rock'))->setEmoji(new Emoji($discord, ['id' => null, 'name' => '🪨']))->setLabel(' ')->setListener(function (Interaction $buttonInteraction) use ($p2, $gameLogic): void {
        if (! buttonPressedByOwner($buttonInteraction) && ! buttonPressedByUser($p2, $buttonInteraction)) {
            return;
        }
        $gameLogic(RpsMove::ROCK, $buttonInteraction);
    }, $discord);

    $buttonPaper = Button::secondary($idCreator->createId('paper'))->setEmoji(new Emoji($discord, ['id' => null, 'name' => '📰']))->setLabel(' ')->setListener(function (Interaction $buttonInteraction) use ($p2, $gameLogic): void {
        if (! buttonPressedByOwner($buttonInteraction) && ! buttonPressedByUser($p2, $buttonInteraction)) {
            return;
        }
        $gameLogic(RpsMove::PAPER, $buttonInteraction);
    }, $discord);

    $buttonScissors = Button::secondary($idCreator->createId('scissors'))->setEmoji(new Emoji($discord, ['id' => null, 'name' => '✂️']))->setLabel(' ')->setListener(function (Interaction $buttonInteraction) use ($p2, $gameLogic): void {
        if (! buttonPressedByOwner($buttonInteraction) && ! buttonPressedByUser($p2, $buttonInteraction)) {
            return;
        }
        $gameLogic(RpsMove::SICSSORS, $buttonInteraction);
    }, $discord);

    $buttons->insert($buttonStart, $buttonDecline, $buttonRock, $buttonPaper, $buttonScissors);

    

    $gamba->games->addGame($game, GameData::create($interaction, $buttons, $idCreator->exportIdMap()));

    $startGameOptions->addComponent($buttonStart);
    $startGameOptions->addComponent($buttonDecline);

    $userMention = Format::mention()->user($p2);
    $interaction->respondWithMessage(MessageBuilder::new()->setContent($userMention.' has been challenged to play rock paper scissors for '.Format::code((string) $bet).' coins')->addComponent($startGameOptions));
});
