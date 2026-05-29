<?php

declare(strict_types=1);

namespace GachaBot;

use Debug\Console\CMDOutput;
use Debug\Debug;
use Discord\Discord;
use Gamba\Gamba;
use Debug\Console\FontColor;
use Debug\MessageType;
use Ewn\Ovent\Event;
use Ewn\Ovent\Interface\EventInterface;
use Ewn\Ovent\trait\EventTrait;
use Infrastructure\FileManager;
use Discord\Parts\User\Activity;
use function GambaBot\set;
use function GambaBot\get;
use function GambaBot\isSafeToTerminate;

final class GachaBot implements EventInterface
{
    use Debug, EventTrait;

    public readonly Discord $discord;

    public readonly Gamba $gamba;

    public private(set) bool $running = false;

    /**
     * Constructor
     *
     * @param string $botToken
     * @param string $databaseHost
     * @param string|null $databaseUsername
     * @param string|null $databasePassword
     * @param string $gambaDatabaseName
     * @param string $inventoryDatabaseName
     * @param string|null $requireFrom Requires all files from a directory
     * @param integer $intents
     */
    public function __construct(
        #[\SensitiveParameter] string $botToken,
        #[\SensitiveParameter] string $databaseHost,
        #[\SensitiveParameter] ?string $databaseUsername,
        #[\SensitiveParameter] ?string $databasePassword,
        string $gambaDatabaseName,
        string $inventoryDatabaseName,
        int $intents,
        private ?string $requireFrom = null,
    ) {
        $this->attachObserver($this);

        date_default_timezone_set(TIME_ZONE);

        $this->discord = new Discord([
            'token' => $botToken,
            'loadAllMembers' => true,
            'intents' => $intents,
        ]);

        // $discord = $this->discord;

        // error_log('PHP version: ' . PHP_VERSION);
        // error_log('DiscordPHP version: ' . \Discord\Discord::VERSION);
        // error_log('ENV DISCORD_TOKEN: ' . (getenv('DISCORD_TOKEN') ? 'present' : 'missing'));
        // error_log('ENV DISCORD_INTENTS: ' . var_export(getenv('DISCORD_INTENTS'), true));

        // $discord->on('raw', function ($payload) {
        //     error_log('RAW: ' . json_encode($payload));
        // });

        // $discord->on('ready', function ($discord) {
        //     error_log('READY fired, setting test listener');
        //     $discord->on('messageCreate', function ($message) {
        //         error_log('MSG recv: ' . $message->content);
        //         if ($message->author->bot) return;
        //         $message->reply('Container echo: ' . substr($message->content, 0, 100));
        //     });
        // });

        $this->gamba = new Gamba(
            gambaDsn: 'mysql:host='.$databaseHost.';port=3306;dbname='.$gambaDatabaseName,
            inventoryManagerDsn: 'mysql:host='.$databaseHost.';port=3306;dbname='.$inventoryDatabaseName,
            username: $databaseUsername,
            password: $databasePassword,
        );

        $this->listenEvent('process.emit.ctrl-event', function (Event $event) {
            $this->discord->updatePresence(new Activity($this->discord, [
                'type' => Activity::TYPE_CUSTOM,
                'name' => 'customStatus',
                'state' => 'Shutting down...',
            ]));
            $this->running = false;
            echo CMDOutput::new()->add(self::createConsoleMessage('Ctrl + C event registered, shutting down at next safe opportunity.', MessageType::INFO), FontColor::BRIGHT_GREEN), PHP_EOL;
            $this->shutDownIfAllowed();
        });

        $this->discord->on('init', fn () => $this->onInit());
    }
    
    public function run(): void
    {
        $this->running = true;
        set('botIsRunning', true);
        set('shutdownCondition', fn () => ! $this->gamba->inventoryManager->activeInventories && ! $this->gamba->games->hasActiveGames);
        $this->emitEvent('running');
        $this->discord->run();
    }

    private function onInit(): void
    {
        $this->discord->updatePresence(new Activity($this->discord, [
            'type' => Activity::TYPE_CUSTOM,
            'name' => 'customStatus',
            'state' => 'Gambling🥰😍',
        ]));

        $this->emitEvent('discord-init');
        $this->discord->on('heartbeat', fn () => $this->onHeartbeat());

        if ($this->requireFrom) {
            FileManager::loadAllFromDir(
                dir: $this->requireFrom,
                fileNameExtension: '.php',
                message: true
            );
        }
        

        echo CMDOutput::new()->add('Online', FontColor::BRIGHT_GREEN), PHP_EOL;
    }

    private function onHeartbeat(): void
    {
        $this->gamba->games->checkTimedEvents();
        $this->gamba->inventoryManager->clearCache();
        $this->gamba->clearCach();
        $this->gamba->printMemory();

        $this->emitEvent('discord-heartbeat');

        $this->shutDownIfAllowed();
    }

    private function shutDownIfAllowed(): void
    {
        if ($this->running === false) {
            isSafeToTerminate()?->endProcess(function () {
                echo CMDOutput::new()->add(self::createConsoleMessage('No games or inventories found, shutting down...', MessageType::INFO), FontColor::BRIGHT_GREEN), PHP_EOL;
                $this->emitEvent('bot-shutdown');
                $this->discord->close(closeLoop: true);
                $this->emitEvent('process.action.end', ['message' => 'Shutting down...']);
            });
            echo CMDOutput::new()->add(self::createConsoleMessage('Found live interactions, delaying shutdown...', MessageType::INFO), FontColor::BRIGHT_YELLOW), PHP_EOL;
        }
    }
}