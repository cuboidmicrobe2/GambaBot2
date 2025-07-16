<?php

declare(strict_types = 1);

namespace Gamba\CoinGame;

use Discord\Builders\Components\ActionRow;
use Debug\Debug;
use Gamba\CoinGame\GameInstance;
use Deprecated;
use Exception;
use SplObjectStorage;
use WeakMap;

/**
 * @todo need to unset button listeners before unset GameInstance ( or not? )
 */
final class GameHandler {
    use Debug;

    private SplObjectStorage $games;
    private WeakMap $gameData;

    public function __construct() {
        $this->games = new SplObjectStorage;
        $this->gameData = new WeakMap; // data is stored in a WeakMap beacuse SplObjectSotrage uses SeekableIterator and prob wont work async but is needed to prevent obj from gc
    }

    public function addGame(GameInstance &$game, GameData $data) : void {

        if($this->IdExists($data->id)) throw new Exception('a GameInstance with the id ' . $data->id . ' already exists');
        if($this->userIsPlaying($data->owner, $game::class)) throw new Exception('the user is already playing that game');
        
        $this->games->attach($game);
        $data->setType($game::class);
        $this->gameData[$game] = $data;
    }

    /**
     * 
     */
    public function closeGame(GameInstance|string $game) : bool {
        if(!$game instanceof GameInstance) {
            $game = $this->getFromId($game);
            if($game === null) return false; //something...
        }

        echo self::createUpdateMessage('', 'removed game ' . json_encode($this->gameData[$game])), PHP_EOL;

        $this->games->detach($game);
        unset($game);
        return true;
    }

    // #[NoDiscard]
    public function getGame(string $interactionId) : ?GameInstance {
        return $this->getFromId($interactionId);
    }

    /**
     * Only works of you used the ComponentIdCreator
     */
    #[Deprecated('use GameHandler::getGame()')]
    public function getGameFromButtonId($id) : ?GameInstance {
        $gameId = explode(':', $id);
        return $this->getFromId($gameId[0]);
    }

    // #[NoDiscard]
    public function getGameData(GameInstance $game) : ?GameData {
        return $this->gameData[$game] ?? null;
    }

    // #[NoDiscard]
    public function getNewActionRow(GameInstance $game) : ActionRow {
        $row = new ActionRow;

        foreach($this->gameData[$game]->buttons as $button) {
            $row->addComponent($button);
        }

        return $row;
    }

    /**
     *  Get GameInstance|false or bool if user is playing a specific or any game
     */
    public function userIsPlaying(string $uid, ?string $game = null, bool $returnGameInstance = false) : GameInstance|bool {

        if($returnGameInstance AND $game === null) throw new Exception('must specify game to return GameInstance');

        if($game === null) {
            foreach($this->gameData as $_ => $data) {
                if($data->owner == $uid) return true;
            }
            return false;
        }

        foreach($this->gameData as $game => $data) {
            if($data->owner == $uid AND $game == $data->gameType) return $returnGameInstance ? $game : true;
        }

        return false;
    }

    public function clean() : void {
        foreach($this->gameData as $game => $data) {
            if($game->expired()) {
                echo self::createUpdateMessage('', 'removed old game ' . json_encode($data)), PHP_EOL;
                $this->games->detach($game);
                unset($game);
            }
        } 
    }

    private function getFromId(string $id) : ?GameInstance {
        foreach($this->gameData as $game => $data) {
            if($data->id == $id) return $game;
        }
        return null;
    }

    private function IdExists(string $id) : bool {
        foreach($this->gameData as $_ => $data) {
            if($data->id == $id) return true;
        }
        return false;
    }
}


/**
 * make close all games command
 */