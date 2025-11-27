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

    // $discord->application->commands->delete('1390392572949827695')->then(function() use ($discord) {
    $discord->application->commands->save(
        $discord->application->commands->create(CommandBuilder::new()
            ->setName('pay')
            ->setDescription('pay another user coins')

            ->addOption(new Option($discord)
                ->setName('amount')
                ->setDescription('amount of coins to pay')
                ->setType(Option::INTEGER)
                ->setRequired(true)
                ->setMinValue(10)
            )

            ->addOption(new Option($discord)
                ->setName('to')
                ->setDescription('the user to pay')
                ->setType(Option::USER)
                ->setRequired(true)
            )

            ->toArray()
        )
    );
});
// });
