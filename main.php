<?php 

declare(strict_types = 1);

use Debug\CMD_FONT_COLOR;
use Discord\Discord;
use Discord\Parts\User\Activity;
use Discord\WebSockets\Intents;
use Symfony\Component\Dotenv\Dotenv;
use Debug\CMDOutput;
use Gamba\Gamba;
use Infrastructure\FileManager;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/defines.php';
require_once __DIR__ . '/Includes/autoload.php';

if (PHP_VERSION_ID < 80407) {
    echo 'You are running an old version of php, use 8.4.7 or later!', PHP_EOL;
    sleep(6);
    exit();
}

date_default_timezone_set(TIME_ZONE);
gc_enable();

set_exception_handler(function(Throwable $e) {
    echo CMDOutput::new()->add($e->getMessage(), CMD_FONT_COLOR::YELLOW), PHP_EOL;
});

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

$gamba = new Gamba(
    gambaDsn: 'mysql:host='.$_ENV['DB_HOSTNAME'].';dbname=gamba',
    inventoryManagerDsn: 'mysql:host='.$_ENV['DB_HOSTNAME'].';dbname=gamba_inventories',
    username: $_ENV['DB_USERNAME'],
    password: $_ENV['DB_PASSWORD'],
);

$discord->on('init', function(Discord $discord) use ($gamba) {

    $discord->updatePresence(new Activity($discord, [
        'type' => Activity::TYPE_CUSTOM,
        'name' => 'customStatus',
        'state' => 'Gambling🥰😍',
    ]));

    $discord->on('heartbeat', function() use ($gamba) {
        $gamba->games->checkTimedEvents();
        $gamba->inventoryManager->clearChace();
        $gamba->printMemory();
    });

    FileManager::loadAllFromDir(
        dir: 'Commands', 
        fileNameExtension: '.php', 
        message: true
    );

    echo CMDOutput::new()->add('Online', CMD_FONT_COLOR::BRIGHT_GREEN), PHP_EOL;
});