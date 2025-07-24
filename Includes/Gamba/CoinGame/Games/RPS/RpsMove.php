<?php

declare(strict_types = 1);

namespace Gamba\CoinGame\Games\RPS;

enum RpsMove {
    case ROCK;
    case PAPER;
    case SICSSORS;

    public function getEmoji() : string {
        return match($this) {
            self::ROCK => '🪨',
            self::PAPER => '📰',
            self::SICSSORS => '✂️',
        };
    }
}