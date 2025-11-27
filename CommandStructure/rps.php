<?php

declare(strict_types=1);

use Discord\Builders\CommandBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Command\Option;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__.'/vendor/autoload.php';

$dotenv = new Dotenv;
$dotenv->load('./.env');

$discord = new Discord([
    'token' => $_ENV['DISCORD_TOKEN'],
]);
$discord->on('init', function (Discord $discord): void {

    // $discord->application->commands->delete('1397588468124614737')->then(function() use ($discord) {
    $discord->application->commands->save(
        $discord->application->commands->create(CommandBuilder::new()
            ->setName('rps')
            ->setDescription('Rock Paper Scissors')

            ->addOption(new Option($discord)
                ->setName('bet')
                ->setDescription('amount of coins to bet')
                ->setMinValue(10)
                ->setType(Option::INTEGER)
                ->setRequired(true)
            )

            ->addOption(new Option($discord)
                ->setName('opponent')
                ->setDescription('user to play against')
                ->setType(Option::USER)
                ->setRequired(true)
            )

            ->toArray()
        )
    );
});
// });
