<?php

declare(strict_types=1);

use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\ApplicationCommand;
use Discord\Parts\Interactions\MessageComponent;
use Gamba\CoinGame\GameData;
use Gamba\CoinGame\Games\ColorGame\ColorGame;
use Gamba\CoinGame\Tools\Components\Factories\ButtonFactory;
use Tools\Discord\Text\Format;

use function GambaBot\Interaction\buttonPressedByOwner;
use function GambaBot\Interaction\getOptionValue;
use function GambaBot\Interaction\getUserId;
use function GambaBot\Interaction\getUsername;
use function GambaBot\Interaction\permissionToRun;

global $gamba, $discord;

// i hate this code

$discord->listenCommand('predictor', function (ApplicationCommand $interaction) use ($discord, $gamba): void {
    if (! permissionToRun($interaction)) {
        return;
    }

    $uid = getUserId($interaction);
    $inventory = $gamba->inventoryManager->getInventory($uid);
    $userCoins = $inventory->getCoins();
    $wager = getOptionValue('amount', $interaction);

    if ($userCoins < $wager) {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('poor fuck'));

        return;
    }

    $inventory->setCoins($userCoins - $wager);

    $actions = function (string $color, ApplicationCommand $interaction, Discord $discord) use ($gamba): array {

        /**
         * @var ?ColorGame
         */
        $game = $gamba->games->getGame($interaction->id);

        $result = $game->guess($color);

        if ($result['win']) {
            $embed = new Embed($discord)->setColor(EMBED_COLOR_PINK)->setTitle('Correct!')
                ->addFieldValues('',
                    <<<'NAME'
                    Coins
                    Multiplier
                    NAME,
                    inline: true
                )
                ->addFieldValues('',
                    <<<VALUES
                    {$game->winnings}
                    x{$game->multiplier}
                    VALUES,
                    inline: true
                );
            $interaction->updateOriginalResponse(MessageBuilder::new()->addEmbed($embed));

            return $result;
        }

        $embed = new Embed($discord)->setColor(EMBED_COLOR_PINK)->setTitle('Wrong!')->setDescription('You lost');
        $interaction->updateOriginalResponse(MessageBuilder::new()->addEmbed($embed));

        $wagerTitleStyled = Format::bold('Wager');
        $rewardTitleStyled = Format::strikeThrough(Format::bold('Reward'));
        $wagerValueStyled = Format::code((string) $game->wager);
        $noWinValueStyled = Format::code('0');

        $interaction->sendFollowUpMessage(MessageBuilder::new()->addEmbed(new Embed($discord)
            ->setTitle('/predictor results for '.getUsername($interaction))
            ->setDescription(Format::bold('Guesses:').' '.$game->historyAsString())
            ->addFieldValues('',
                <<<NAME
                {$wagerTitleStyled}
                {$rewardTitleStyled}
                NAME,
                inline: true
            )
            ->addFieldValues('',
                <<<VALUES
                {$wagerValueStyled}
                {$noWinValueStyled}
                VALUES,
                inline: true
            )
            ->setColor(EMBED_COLOR_RED)
        ));

        $gamba->games->closeGame($game);

        return $result;
    };

    $game = new ColorGame($wager);

    $embed = new Embed($discord)->setColor(EMBED_COLOR_PINK)->setTitle('Make a guess!')
        ->addFieldValues('',
            <<<'NAME'
            Coins
            Multiplier
            NAME,
            inline: true
        )
        ->addFieldValues('',
            <<<VALUES
            {$game->winnings}
            x{$game->multiplier}
            VALUES,
            inline: true
        );

    $buttonFactory = new ButtonFactory($interaction);

    $buttonFactory->create(Button::STYLE_SUCCESS, 'green')->setLabel('Green')->setListener(function (MessageComponent $buttonInteraction) use ($interaction, $actions, $discord): void {
        if (! buttonPressedByOwner($buttonInteraction)) {
            return;
        }
        $actions('green', $interaction, $discord);
    }, $discord);

    $buttonFactory->create(Button::STYLE_DANGER, 'red')->setLabel('Red')->setListener(function (MessageComponent $buttonInteraction) use ($interaction, $actions, $discord): void {
        if (! buttonPressedByOwner($buttonInteraction)) {
            return;
        }
        $actions('red', $interaction, $discord);
    }, $discord);

    $buttonFactory->create(Button::STYLE_SECONDARY, 'end_game')->setLabel('End Game')->setListener(function (MessageComponent $buttonInteraction) use ($gamba, $interaction, $discord): void {
        if (! buttonPressedByOwner($buttonInteraction)) {
            return;
        }
        $inventory = $gamba->inventoryManager->getInventory(getUserId($interaction));

        /**
         * @var ?ColorGame
         */
        $game = $gamba->games->getGame($interaction->id);
        $finalWin = $game->winnings;
        $interaction->updateOriginalResponse(MessageBuilder::new()->setContent('Game ended! You won: '.$finalWin.' coins'));

        $wagerTitleStyled = Format::bold('Wager');
        $rewardTitleStyled = Format::bold('Reward');
        $wagerValueStyled = Format::code((string) $game->wager);
        $rewardValueStyled = Format::code((string) $game->winnings);

        $interaction->sendFollowUpMessage(MessageBuilder::new()->addEmbed(new Embed($discord)
            ->setTitle('/predictor results for '.getUsername($interaction))
            ->setDescription(Format::bold('Guesses:').' '.$game->historyAsString())
            ->addFieldValues('',
                <<<NAME
                {$wagerTitleStyled}
                {$rewardTitleStyled}
                NAME,
                inline: true
            )
            ->addFieldValues('',
                <<<VALUES
                {$wagerValueStyled}
                {$rewardValueStyled}
                VALUES,
                inline: true
            )
            ->setColor(EMBED_COLOR_GREEN)
        ));
        $gamba->games->closeGame($game);

        $coins = $inventory->getCoins();

        $inventory->setCoins($coins + (int) $finalWin);

    }, $discord);

    $gamba->games->addGame($game, GameData::create($interaction, $buttonFactory->createCollection(), $buttonFactory->getMap()));

    $interaction->respondWithMessage(MessageBuilder::new()->addEmbed($embed)->addComponent($buttonFactory->createActionRow()));
});
