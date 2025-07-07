<?php

namespace Gamba\CoinGame;

final class GameData {
    public private(set) string $id;
    public private(set) string $owner;
    public private(set) string $timeOfCreation;

    private function __construct(string $id, string $owner) {
        $this->timeOfCreation = time();
    }

    public static function create(string $id, string $owner) : self {
        $s = new self($id, $owner);
        return $s;
    }
}