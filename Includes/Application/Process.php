<?php

declare(strict_types=1);

namespace Application;

use Ewn\Ovent\Event;
use Ewn\Ovent\Interface\EventInterface;
use Ewn\Ovent\Trait\EventTrait;

final class Process implements EventInterface
{
    use EventTrait;

    public function __construct()
    {
        $this->setCtrlHandler();
        $this->listenEvent('process.action.end', function (Event $event) {
            $this->end($event);
        });
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
}
