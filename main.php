<?php declare(strict_types = 1);

use Debug\CMD_FONT_COLOR;
use Discord\Discord;
use Discord\Parts\User\Activity;
use Discord\WebSockets\Intents;
use Symfony\Component\Dotenv\Dotenv;
use Debug\CMDOutput;
use Discord\Parts\Interactions\Interaction;
use Discord\WebSockets\Event;
use Gamba\Gamba;
use Gamba\Loot\Item\InventoryManager;
use Infrastructure\FileManager;

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

$gamba = new Gamba(
    itemConn:           PDO::connect('mysql:host='.$_ENV['DB_HOSTNAME'].';dbname=gamba', $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']),
    inventoryManager:   new InventoryManager(PDO::connect('mysql:host='.$_ENV['DB_HOSTNAME'].';dbname=gamba_inventories', $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']))
);

$discord->on('init', function(Discord $discord) {
    $discord->updatePresence(new Activity($discord, [
        'type' => Activity::TYPE_GAME,
        'name' => 'the long game',
    ]));

    $discord->on(Event::INTERACTION_CREATE, function(Interaction $interaction) {
        // interaction debug'n
    });

    FileManager::loadAllFromDir('Commands', '.php', true);

    echo CMDOutput::new()->add('Online', CMD_FONT_COLOR::BRIGHT_GREEN), PHP_EOL;
});