<?php

namespace Gamba\CoinGame;

use Gamba\CoinGame\GameInstance;
use SplObjectStorage;
use WeakMap;

final class GameHandler {
    private SplObjectStorage $games;
    private WeakMap $gameData;

    public function __construct() {
        $this->games = new SplObjectStorage;
        $this->gameData = new WeakMap; // data is stored in a WeakMap beacuse SplObjectSotrage uses SeekableIterator and prob wont work async but is needed to prevent obj from gc
    }

    public function addGame(GameInstance &$game, GameData $data) : void {
        $this->games->attach($game);
        $this->gameData[$game] = $data;
    }

    public function closeGame(string $interactionId) : bool {
        $game = $this->getGameFromKey('interaction', $interactionId);
        if($game === null) return false; //something...

        $this->games->detach($game);
        unset($game);
        return true;
    }

    public function getGame(string $interactionId) : ?GameInstance {
        return $this->getGameFromKey('interaction', $interactionId);
    }

    public function clean() : void {
        foreach($this->gameData as $game => $_) {
            if($game->expired()) {
                $this->games->detach($game);
                unset($game);
            }
        } 
    }

    private function getGameFromKey(string $key, string $value) : ?GameInstance {
        foreach($this->gameData as $game => $data) {
            if($data[$key] == $value) return $game;
        }
        return null;
    }
}