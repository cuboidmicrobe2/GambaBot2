<?php

declare(strict_types=1);

use Discord\Builders\CommandBuilder;
use Discord\Discord;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__.'/vendor/autoload.php';

$dotenv = new Dotenv;
$dotenv->load('./.env');

$discord = new Discord([
    'token' => $_ENV['DISCORD_TOKEN'],
]);
$discord->on('init', function (Discord $discord): void {

    // $discord->application->commands->delete('')->then(function() use ($discord) {
    $discord->application->commands->save(
        $discord->application->commands->create(CommandBuilder::new()
            ->setName('news')
            ->setDescription('get the latest bot news')
            ->toArray()
        )
    );
});
// });
