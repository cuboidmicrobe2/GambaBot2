<?php

declare(strict_types=1);

use CMDFontColor;
use Debug\Console\CMDOutput;
use Debug\Console\FontColor;
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
                    $content = self::createConsoleMessage('Ctrl + C event registered, shutting down at next safe opportunity.', MessageType::INFO);

                    return CMDOutput::create(FontColor::BRIGHT_GREEN, $content);
                }
            }(), PHP_EOL;

            GambaBot\set('botIsRunning', false);

            return;
        default:
            return;
    }
};
