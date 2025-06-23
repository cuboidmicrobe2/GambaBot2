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
    $discord->application->commands->delete('1250871135428415488')->then(function() use ($discord) {
        $discord->application->commands->save(
            $discord->application->commands->create(CommandBuilder::new()
                ->setName('wish')
                ->setDescription('Wish for items (1000 coins per roll)')

                ->addOption(new Option($discord)
                    ->setName('amount')
                    ->setDescription('amount of wishes')
                    ->setRequired(true)
                    ->setType(Option::INTEGER)

                    ->addChoice(new Choice($discord)
                        ->setName('1')
                        ->setValue(1)
                    )

                    ->addChoice(new Choice($discord)
                        ->setName('10')
                        ->setValue(10)
                    )
                )

                ->toArray()
            )
        );
    });


});




