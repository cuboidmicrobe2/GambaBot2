<?php

use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Option;
use Symfony\Component\Dotenv\Dotenv;
use Discord\Discord;
use Discord\Parts\Interactions\Command\Choice;

require_once './vendor/autoload.php';


$dotenv = new Dotenv;
$dotenv->load('./.env');


$discord = new Discord([
    'token' => $_ENV['DISCORD_TOKEN'],
]);
$discord->on('init', function(Discord $discord) {

    //$discord->application->commands->delete('1385704701559050400')->then(function() use ($discord) {
        $discord->application->commands->save(
            $discord->application->commands->create(CommandBuilder::new()
                ->setName('rps')
                ->setDescription('Rock Paper Scissors')

                ->addOption(new Option($discord)
                    ->setName('bet')
                    ->setDescription('amount of coins to bet')
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
//});
