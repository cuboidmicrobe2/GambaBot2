<?php

declare(strict_types = 1);

use Discord\Builders\CommandBuilder;
use Symfony\Component\Dotenv\Dotenv;
use Discord\Discord;

require_once './vendor/autoload.php';


$dotenv = new Dotenv;
$dotenv->load('./.env');


$discord = new Discord([
    'token' => $_ENV['DISCORD_TOKEN'],
]);
$discord->on('init', function(Discord $discord) {

    //$discord->application->commands->delete('')->then(function() use ($discord) {
        $discord->application->commands->save(
            $discord->application->commands->create(CommandBuilder::new()
                ->setName('news')
                ->setDescription('get the latest bot news')
                ->toArray()
            )
        );
    });
//});
