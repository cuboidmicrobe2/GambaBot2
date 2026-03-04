<?php

declare(strict_types=1);

namespace Application;

use Ewn\Ovent\Event;
use Ewn\Ovent\Interface\EventInterface;
use Ewn\Ovent\Trait\EventTrait;
use Ewn\Ovent\Listener;
use InvalidArgumentException;

final class Process implements EventInterface
{
    use EventTrait;

    public const string ACTION_END = 'process.action.end';

    public readonly int $PID;

    /**
     * Undocumented variable
     *
     * @var array<string, Listener>
     */
    private array $listeners = [];

    public function __construct()
    {
        $pid = getmypid();
        $this->PID = $pid ? $pid : -1;
        $this->setCtrlHandler();
        $this->on(self::ACTION_END, fn (Event $event) => $this->end($event));
    }

    public function updateListener(string $event, callable $callback): void
    {
        $listener = $this->listeners[$event] ?? null;

        if ($listener === null) {
            throw new InvalidArgumentException($event.' is not a valid event listener on this '.self::class);
        }

        $listener->replaceCallback($callback);
    }
    
    private function end(Event $event): never
    {
        if (isset($event->detail['message'])) {
            echo $event->detail['message'], PHP_EOL;
        }

        $code = isset($event->detail['code']) ? (int) $event->detail['message'] : 0;

        exit($code);
    }

    private function setCtrlHandler(): void
    {
        sapi_windows_set_ctrl_handler(function ($event) {
            switch ($event) {
                case PHP_WINDOWS_EVENT_CTRL_C:
                    $this->emitEvent('process.emit.ctrl-event');
                    return;
                default:
                    return;
            }
        });
    }

    private function on(string $event, callable $callback): void
    {
        $this->listeners[$event] = $this->listenEvent($event, $callback);
    }
}
