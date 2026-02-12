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
use function GambaBot\set;
use function GambaBot\get;
use function GambaBot\isSafeToTerminate;

final class GachaBot implements EventInterface
{
    use Debug, EventTrait;

    public readonly Discord $discord;

    public readonly Gamba $gamba;

    public private(set) bool $running;

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
        private ?string $requireFrom = null,
        int $intents
    ) {
        date_default_timezone_set(TIME_ZONE);

        $this->discord = new Discord([
            'token' => $botToken,
            'loadAllMembers' => true,
            'intents' => $intents,
        ]);

        $this->gamba = new Gamba(
            gambaDsn: 'mysql:host='.$databaseHost.';dbname='.$gambaDatabaseName,
            inventoryManagerDsn: 'mysql:host='.$databaseHost.';dbname='.$inventoryDatabaseName,
            username: $databaseUsername,
            password: $databasePassword,
        );
        // $this->setCtrlHandler();
        $this->listenEvent('process.emit.ctrl-event', function (Event $event) {
            $this->running = false;
            echo CMDOutput::new()->add(self::createConsoleMessage('Ctrl + C event registered, shutting down at next safe opportunity.', MessageType::INFO), FontColor::BRIGHT_GREEN), PHP_EOL;
        });
        $this->discord->on('init', fn () => $this->onInit());
    }
    
    public function run(): void
    {
        set('botIsRunning', true);
        set('shutdownCondition', fn () => ! $this->gamba->inventoryManager->activeInventories && ! $this->gamba->games->hasActiveGames);
        $this->emitEvent('running');
        $this->discord->run();
    }

    // private function setCtrlHandler(): void
    // {
    //     sapi_windows_set_ctrl_handler(function ($event) {
    //         switch ($event) {
    //             case PHP_WINDOWS_EVENT_CTRL_C:
    //                 echo CMDOutput::new()->add(self::createConsoleMessage('Ctrl + C event registered, shutting down at next safe opportunity.', MessageType::INFO), FontColor::BRIGHT_GREEN), PHP_EOL;
    //                 set('botIsRunning', false);
    //                 $this->emitEvent('ctrl-c');
    //                 return;
    //             default:
    //                 return;
    //         }
    //     });
    // }

    private function onInit(): void
    {
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