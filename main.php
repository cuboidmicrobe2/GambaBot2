<?php declare(strict_types = 1);

use Discord\Discord;
use Discord\Parts\User\Activity;
use Discord\WebSockets\Intents;
use Symfony\Component\Dotenv\Dotenv;
use Debug\CMDOutput;
use Discord\Parts\Interactions\Interaction;
use Discord\WebSockets\Event;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Includes/autoload.php';

const AUTO_LOADER = new Autoloader(
    flags:Autoloader::DO_OUTPUT
);
AUTO_LOADER->start();

$dotenv = new Dotenv;
$dotenv->load(__DIR__ . '/.env');


$discord = new Discord([
    'token' => $_ENV['DISCORD_TOKEN'],
    'loadAllMembers' => true,
    'intents' => Intents::getDefaultIntents() | Intents::GUILDS | Intents::GUILD_MEMBERS,
]);

$discord->on('init', function(Discord $discord) {
    $discord->updatePresence(new Activity($discord, [
        'type' => Activity::TYPE_GAME,
        'name' => 'the long game',
    ]));

    $discord->on(Event::INTERACTION_CREATE, function(Interaction $interaction) {
        // interaction debug'n
    });

    echo CMDOutput::new()->add('Online', 92), PHP_EOL;
});