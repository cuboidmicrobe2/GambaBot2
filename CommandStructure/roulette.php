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

   // $discord->application->commands->delete('1385704701559050400')->then(function() use ($discord) {
        $discord->application->commands->save(
            $discord->application->commands->create(CommandBuilder::new()
                ->setName('roulette')
                ->setDescription('roulette on 0-38 and 0 is green')

                ->addOption(new Option($discord)
                    ->setName('color')
                    ->setDescription('select a color')
                    ->setType(Option::STRING)
                    ->setRequired(true)

                    ->addChoice(new Choice($discord)
                        ->setName('green')
                        ->setValue('3')
                    )

                    ->addChoice(new Choice($discord)
                        ->setName('red')
                        ->setValue('2')
                    )

                    ->addChoice(new Choice($discord)
                        ->setName('black')
                        ->setValue('1')
                    )
                )

                ->addOption(new Option($discord)
                    ->setName('amount')
                    ->setDescription('amount of coins to gamble')
                    ->setRequired(true)
                    ->setType(Option::INTEGER)
                    ->setMinValue(10)
                )
                ->toArray()
            )
        );
    });
    

//});

