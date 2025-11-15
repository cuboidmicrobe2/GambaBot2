<?php

declare(strict_types=1);

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\ApplicationCommand;
use Discord\Parts\Interactions\MessageComponent;
use Gamba\CoinGame\Tools\Components\ButtonCollection;
use Gamba\CoinGame\Tools\Components\ComponentIdCreator;
use Gamba\CoinGame\Tools\Components\ComponentType;
use Gamba\CoinGame\GameData;
use Gamba\CoinGame\Games\ColorGame\ColorGame;
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
        $wagerValueStyled = Format::code((string)$game->wager);
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

    $row = new ActionRow;

    $buttons = new ButtonCollection(3);

    $idCreator = new ComponentIdCreator($interaction);

    $greenButton = Button::success($idCreator->createId('green', ComponentType::BUTTON))->setLabel('Green')->setListener(function (MessageComponent $buttonInteraction) use ($interaction, $actions, $discord): void {
        if (! buttonPressedByOwner($buttonInteraction)) {
            return;
        }
        $result = $actions('green', $interaction, $discord);
    }, $discord);
    $buttons[0] = $greenButton;
    $row->addComponent($greenButton);

    $redButton = Button::danger($idCreator->createId('red', ComponentType::BUTTON))->setLabel('Red')->setListener(function (MessageComponent $buttonInteraction) use ($interaction, $actions, $discord): void {
        if (! buttonPressedByOwner($buttonInteraction)) {
            return;
        }
        $result = $actions('red', $interaction, $discord);
    }, $discord);
    $buttons[1] = $redButton;
    $row->addComponent($redButton);

    // $blueButton = Button::primary($idCreator->createId('blue'))->setLabel('Blue')->setListener(function(Interaction $buttonInteraction) use ($interaction, $actions, $userCoins,) {
    //     $result = $actions('blue', $interaction);
    // }, $discord);
    // $buttons[2] = $redButton;

    $endButton = Button::secondary($idCreator->createId('end_game', ComponentType::BUTTON))->setLabel('End Game')->setListener(function (MessageComponent $buttonInteraction) use ($gamba, $interaction, $discord): void {
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
        $wagerValueStyled = Format::code((string)$game->wager);
        $rewardValueStyled = Format::code((string)$game->winnings);

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

        $inventory->setCoins($coins + (int)$finalWin);

    }, $discord);
    $buttons[2] = $endButton;
    $row->addComponent($endButton);

    $gamba->games->addGame($game, GameData::create($interaction, $buttons, $idCreator->exportIdMap()));

    $interaction->respondWithMessage(MessageBuilder::new()->addEmbed($embed)->addComponent($row));

    // --------------------------------------------------------------------------------------------------------------

    // $ButtonId = new ComponentIdCreator($interaction);

    // $game = new TestGame;
    // $buttons = new ButtonCollection(1);

    // $button1 = Button::success($ButtonId->createId('button_name_1'))->setLabel('test 1')->setListener(function(Interaction $buttonInteraction) use ($gamba, $interaction) {

    //     $buttonId = $buttonInteraction->data->custom_id;

    //     var_dump($buttonId);
    //     $game = $gamba->games->getGame($interaction->id);
    //     var_dump($game);
    //     $message = MessageBuilder::new()->setContent($game->getNext());

    //     $gamba->games->getGameData($game)->updateMessage($message);
    //     // $interaction->updateOriginalResponse($message);
    // }, $discord);

    // $buttons[0] = $button1;

    // $gamba->games->addGame($game, GameData::create($interaction->id, getUserId($interaction), $buttons, $interaction));
    // // var_dump($button1->getCustomId());
    // $row = ActionRow::new()->addComponent($button1);

    // $interaction->respondWithMessage(MessageBuilder::new()->setContent($game->getNext())->addComponent($row), true);
});
