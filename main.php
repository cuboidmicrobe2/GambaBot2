<?php declare(strict_types = 1);

use Discord\Discord;
use Discord\Parts\User\Activity;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;
use Symfony\Component\Dotenv\Dotenv;
require_once __DIR__ . '/vendor/autoload.php';

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



    echo 'GambaBot is online.';
});