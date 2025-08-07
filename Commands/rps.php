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

use function GambaBot\Discord\mention;
use function GambaBot\Discord\TextStyle\code;
use function GambaBot\Discord\TextStyle\italic;
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
        $interaction->respondWithMessage(MessageBuilder::new()->setContent(italic('Do you not have any friends?')), ephemeral: true);

        return;
    }

    $bet = getOptionValue('bet', $interaction);

    $p1Inv = $gamba->inventoryManager->getInventory($p1);
    $p2Inv = $gamba->inventoryManager->getInventory($p2);

    if ($p1Inv->getCoins() < $bet || $p2Inv->getCoins() < $bet) {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('One or two players does not have enough coins to play'), ephemeral: true);

        return;
    }
    $game = new RockPaperScissors($p1, $p1Inv, $p2, $p2Inv, $bet);
    $buttons = new ButtonCollection(5);
    $startGameOptions = new ActionRow;
    $idCreator = new ComponentIdCreator($interaction);

    $gameLogic = function (RpsMove $move, Interaction $buttonInteraction) use ($interaction, $gamba, $discord, $p1, $p2): void {
        $player = buttonPresserId($buttonInteraction);

        /**
         * @var ?RockPaperScissors
         */
        $game = $gamba->games->getGame($interaction->id);
        $gameData = $gamba->games->getGameData($game);

        $p1Name = $gameData->data[$p1];
        $p2Name = $gameData->data[$p2];

        if ($game->makeMove($player, $move)) {

            if ($game->movesDone()) {

                $moves = $game->roundData[$game->round];

                $game->executeRound();

                if ($winner = $game->checkWinner()) {

                    $interaction->updateOriginalResponse(MessageBuilder::new()->addEmbed(new Embed($discord)
                        ->setTitle($p1Name.' '.$moves[$p1]->getEmoji().' ['.code($game->p1Points.' - '.$game->p2Points).'] '.$moves[$p2]->getEmoji().' '.$p2Name)
                        ->setDescription('Round: '.code((string) ($game->round - 1)))
                        ->setColor(EMBED_COLOR_PINK)
                    ));

                    $winnerUsername = '$name';
                    $discord->users->fetch($winner)->then(function (User $user) use (&$winnerUsername): void {
                        $winnerUsername = getUsername($user);
                    });

                    $scoreFormatted = '$score';
                    if ($game->p1Points > $game->p2Points) {
                        $scoreFormatted = code($game->p1Points.' - '.$game->p2Points);
                    } else {
                        $scoreFormatted = code($game->p2Points.' - '.$game->p1Points);
                    }

                    $p1MoveHistory = '';
                    $p2MoveHistory = '';

                    foreach ($game->roundData as $moves) {
                        $p1MoveHistory .= $moves[$p1]?->getEmoji().' ';
                        $p2MoveHistory .= $moves[$p2]?->getEmoji().' ';
                    }

                    $interaction->sendFollowUpMessage(MessageBuilder::new()->addEmbed(new Embed($discord)
                        ->setTitle($winnerUsername.' winns '.$scoreFormatted)
                        ->setDescription('Coins: '.code((string) $game->bet))
                        ->setColor(EMBED_COLOR_GREEN)
                        ->addFieldValues(
                            'Players',
                            <<<PLAYERS
                            {$gameData->data[$p1]}
                            {$gameData->data[$p2]}
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
                    ->setTitle(italic($p1Name).' '.$moves[$p1]->getEmoji().' ['.code($game->p1Points.' - '.$game->p2Points).'] '.$moves[$p2]->getEmoji().' '.italic($p2Name))
                    ->setDescription('Round: '.code((string) $game->round))
                    ->setColor(EMBED_COLOR_PINK)
                ));

                return;
            }

            $p1Move = '';
            $p2Move = '';

            if ($game->round > 1) {
                $p1Move = ' '.$game->roundData[$game->round - 1][$p1]->getEmoji();
                $p2Move = $game->roundData[$game->round - 1][$p2]->getEmoji().' ';
            }

            $p1NameStyled = ($game->roundData[$game->round][$p1] === null) ? italic($p1Name) : $p1Name;
            $p2NameStyled = ($game->roundData[$game->round][$p2] === null) ? italic($p2Name) : $p2Name;
            $interaction->updateOriginalResponse(MessageBuilder::new()->addEmbed(new Embed($discord)
                ->setTitle($p1NameStyled.$p1Move.' ['.code($game->p1Points.' - '.$game->p2Points).'] '.$p2Move.$p2NameStyled)
                ->setDescription('Round: '.code((string) $game->round))
                ->setColor(EMBED_COLOR_PINK)
            ));
        }
    };

    $buttonStart = Button::success($idCreator->createId('accept'))->setLabel('Accept')->setListener(function (Interaction $buttonInteraction) use ($p1, $p2, $discord, $gamba, $interaction, $idCreator, $p1Inv, $p2Inv, $bet): void {
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
        $gameData->removeButton($idCreator->getId('decline'));
        $gameData->removeButton($idCreator->getId('accept'));
        $gameOptions = $gamba->games->getNewActionRow($game);

        $game->renew();
        $game->started = true;

        $interaction->updateOriginalResponse(MessageBuilder::new()->setContent('')->addComponent($gameOptions)->addEmbed(new Embed($discord)
            ->setTitle(italic($gameData->data[$p1]).' ['.code('0 - 0').'] '.italic($gameData->data[$p2]))
            ->setDescription('Round: '.code((string) $game->round))
            ->setColor(EMBED_COLOR_PINK)
        ));
    }, $discord);

    $buttonDecline = Button::danger($idCreator->createId('decline'))->setLabel('Decline')->setListener(function (Interaction $buttonInteraction) use ($p2, $gamba, $interaction): void {
        if (! buttonPressedByUser($p2, $buttonInteraction)) {
            return;
        }
        $game = $gamba->games->getGame($interaction->id);
        $gamba->games->closeGame($game);

        $interaction->updateOriginalResponse(MessageBuilder::new()->setContent(italic('The match was declined')));
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

    $p1Name = '$name';
    $p2Name = '$name';
    $discord->users->fetch($p1)->then(function (User $user) use (&$p1Name): void {
        $p1Name = getUsername($user);
    });
    $discord->users->fetch($p2)->then(function (User $user) use (&$p2Name): void {
        $p2Name = getUsername($user);
    });

    $gamba->games->addGame($game, GameData::create($interaction, $buttons, $idCreator->exportIdMap(), data: [$p1 => $p1Name, $p2 => $p2Name]));

    $startGameOptions->addComponent($buttonStart);
    $startGameOptions->addComponent($buttonDecline);

    $interaction->respondWithMessage(MessageBuilder::new()->setContent(mention($p2).' has been challenged to play rock paper scissors for '.code((string) $bet).' coins')->addComponent($startGameOptions));
});
