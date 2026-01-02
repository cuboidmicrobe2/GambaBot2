<?php

declare(strict_types=1);

use GatchaBot\GatchaBot;
use Debug\Console\CMDOutput;
use Debug\Console\FontColor;
use Discord\WebSockets\Intents;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/functions.php';
require_once __DIR__.'/defines.php';

if (PHP_VERSION_ID < 80500) {
    echo CMDOutput::create(FontColor::YELLOW, 'You are running an old version of php ('.PHP_VERSION.'), GachaBot requires version 8.5.0 or later!'), PHP_EOL;
    sleep(10);
    exit();
}

set_exception_handler(function (Throwable $e) {
    echo CMDOutput::create(FontColor::YELLOW, $e->getMessage()), PHP_EOL;
});

$dotenv = new Dotenv;
$dotenv->load(__DIR__.'/.env');

$gatchaBot = new GatchaBot(
    botToken: $_ENV['DISCORD_TOKEN'],
    databaseHost: $_ENV['DB_HOSTNAME'],
    databaseUsername: $_ENV['DB_USERNAME'],
    databasePassword: $_ENV['DB_PASSWORD'],
    gambaDatabaseName: 'gamba',
    inventoryDatabaseName: 'gamba_inventories',
    intents: Intents::getDefaultIntents() | Intents::GUILDS | Intents::GUILD_MEMBERS
);

$discord = $gatchaBot->discord;
$gamba = $gatchaBot->gamba;

$gatchaBot->run();
