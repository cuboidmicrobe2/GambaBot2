<?php

declare(strict_types=1);

use Debug\CMD_FONT_COLOR;
use Debug\CMDOutput;
use Debug\Debug;
use Debug\MessageType;
use Discord\Discord;
use Discord\Parts\User\Activity;
use Discord\WebSockets\Intents;
use Gamba\Gamba;
use Infrastructure\FileManager;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/functions.php';
require_once __DIR__.'/defines.php';

if (PHP_VERSION_ID < 80500) {
    echo CMDOutput::new()->add('You are running an old version of php ('.PHP_VERSION.'), GachaBot requires version 8.5.0 or later!', CMD_FONT_COLOR::YELLOW), PHP_EOL;
    sleep(10);
    exit();
}

date_default_timezone_set(TIME_ZONE);
gc_enable();

set_exception_handler(function (Throwable $e) {
    echo CMDOutput::new()->add($e->getMessage(), CMD_FONT_COLOR::YELLOW), PHP_EOL;
});

sapi_windows_set_ctrl_handler(include __DIR__.'/ctrl_handler.php');

$dotenv = new Dotenv;
$dotenv->load(__DIR__.'/.env');

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

$messageBuilder = new class
{
    use Debug;

    public function createMessage(string $message, CMD_FONT_COLOR $color): string
    {
        $content = self::createUpdateMessage('', $message, MessageType::INFO);

        return CMDOutput::new()->add($content, $color).PHP_EOL;
    }
};

GambaBot\set('shutdownCondition', fn () => ! $gamba->inventoryManager->activeInventories && ! $gamba->games->hasActiveGames);

$discord->on('init', function (Discord $discord) use ($gamba, $messageBuilder) {

    GambaBot\set('botIsRunning', true);

    $discord->updatePresence(new Activity($discord, [
        'type' => Activity::TYPE_CUSTOM,
        'name' => 'customStatus',
        'state' => 'Gambling🥰😍',
    ]));

    $discord->on('heartbeat', function () use ($discord, $gamba, $messageBuilder) {
        $gamba->games->checkTimedEvents();
        $gamba->inventoryManager->clearChace();
        $gamba->clearCach();
        $gamba->printMemory();

        if (GambaBot\get('botIsRunning') === false) {
            GambaBot\isSafeToTerminate()?->endProcess(function () use ($discord, $messageBuilder) {
                echo $messageBuilder->createMessage('No games or inventories found, shutting down...', CMD_FONT_COLOR::BRIGHT_GREEN);
                $discord->close(closeLoop: true);
            });
            echo $messageBuilder->createMessage('Found live interactions, delaying shutdown...', CMD_FONT_COLOR::BRIGHT_YELLOW);
        }
    });

    FileManager::loadAllFromDir(
        dir: 'Commands',
        fileNameExtension: '.php',
        message: true
    );

    echo CMDOutput::new()->add('Online', CMD_FONT_COLOR::BRIGHT_GREEN), PHP_EOL;
});

$discord->run();
