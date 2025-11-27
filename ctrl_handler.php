<?php

declare(strict_types=1);

use Debug\CMD_FONT_COLOR;
use Debug\CMDOutput;
use Debug\Debug;
use Debug\MessageType;

return function ($event) {
    switch ($event) {
        case PHP_WINDOWS_EVENT_CTRL_C:
            echo new class
            {
                use Debug;

                public function __invoke()
                {
                    $content = self::createUpdateMessage('', 'Ctrl + C event registered, shutting down at next safe opportunity.', MessageType::INFO);

                    return CMDOutput::new()->add($content, CMD_FONT_COLOR::BRIGHT_GREEN);
                }
            }(), PHP_EOL;

            GambaBot\set('botIsRunning', false);

            return;
        default:
            return;
    }
};
