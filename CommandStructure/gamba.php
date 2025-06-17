<?php

use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Option;
use Symfony\Component\Dotenv\Dotenv;
use Discord\Discord;


require_once './vendor/autoload.php';


$dotenv = new Dotenv;
$dotenv->load('./.env');


$discord = new Discord([
    'token' => $_ENV['DISCORD_TOKEN'],
]);
$discord->on('init', function(Discord $discord) {
    $discord->application->commands->save(
        $discord->application->commands->create(CommandBuilder::new()
            ->setName('gamba')
            ->setDescription('Gamble for items (x price per roll)')

            ->addOption(new Option($discord)
                ->setName('rolls')
                ->setDescription('amount of rolls')
                ->setType(Option::INTEGER)
                ->setRequired(true)
            )
        )
    );
});




