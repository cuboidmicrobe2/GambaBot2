<?php

declare(strict_types=1);

namespace Gamba\CoinGame;

use Debug\Debug;
use Deprecated;
use Discord\Builders\Components\ActionRow;
use Gamba\CoinGame\MultiInteractionLink;
use Exception;
use InvalidArgumentException;
use SplObjectStorage;
use TimedGameInstance;
use WeakMap;

use function GambaBot\Tools\isUsing;

/**
 * 
 */
final class GameHandler
{
    use Debug;

    private SplObjectStorage $games;

    /**
     * @var WeakMap<GameInstance, GameData|GameDataMap>
     */
    private WeakMap $gameData;

    public function __construct()
    {
        $this->games = new SplObjectStorage;
        $this->gameData = new WeakMap; // data is stored in a WeakMap beacuse SplObjectSotrage uses SeekableIterator and prob wont work async but is needed to prevent obj from gc
    }

    public function addGame(GameInstance &$game, GameData|GameDataMap $data): void
    {

        if ($this->IdExists($data->id)) {
            throw new Exception('a GameInstance with the id '.$data->id.' already exists');
        }
        // if ($this->userIsPlaying($data->owner, $game::class)) {
        //     throw new Exception('the user is already playing that game');
        // }

        if (! $data instanceof GameDataMap && isUsing($game, MultiInteractionLink::class)) {
            throw new InvalidArgumentException('Games with a MultiInteractionLink must pass a GameDataMap');
        }

        if ($data instanceof GameDataMap && ! isUsing($game, MultiInteractionLink::class)) {
            throw new InvalidArgumentException('Games without a MultiInteractionLink must not use a GameDataMap');
        }

        $this->games->attach($game);
        $data->setType($game::class);
        $this->gameData[$game] = $data;
    }

    // /**
    //  * @param GameInstance<MultiInteractionLink> $game
    //  */
    // public function addLinkedGame(GameInstance &$game, array $data): void
    // {
    //     if (! isUsing($game, MultiInteractionLink::class)) {
    //         throw new InvalidArgumentException($game::class.' does not use the MultiInteractionLink trait');
    //     }
    // }

    public function closeGame(GameInstance|string $game): bool
    {
        if (! $game instanceof GameInstance) {
            $game = $this->getFromId($game);
            if (!$game instanceof GameInstance) {
                return false;
            } // something...
        }

        echo self::createUpdateMessage('', 'removed game '.json_encode($this->gameData[$game])), PHP_EOL;

        $this->games->detach($game);
        unset($game);

        return true;
    }

    // #[NoDiscard]
    public function getGame(string $interactionId): ?GameInstance
    {
        return $this->getFromId($interactionId);
    }

    /**
     * Only works of you used the ComponentIdCreator
     */
    #[Deprecated('use GameHandler::getGame()')]
    public function getGameFromButtonId($id): ?GameInstance
    {
        $gameId = explode(':', (string) $id);

        return $this->getFromId($gameId[0]);
    }

    // #[NoDiscard]
    public function getGameData(GameInstance $game, ?string $interactionId = null): GameData
    {
        $data = $this->gameData[$game] ?? null;

        if ($data === null) {
            throw new InvalidArgumentException('Game does not have data');
        }

        if ($data instanceof GameDataMap) {
            if ($interactionId === null) {
                throw new InvalidArgumentException('This game is an interaction link so the $interactionId must not be null');
            }

            return $data->get($interactionId);
        }

        return $data;
    }

    // public function getLinkedData(GameInstance $game, string $interactionId): GameData
    // {
    //     if (! isUsing($game, MultiInteractionLink::class)) {
    //         throw new InvalidArgumentException($game::class.' does not use the MultiInteractionLink trait');
    //     }


    // }

    // #[NoDiscard]
    public function getNewActionRow(GameInstance $game): ActionRow
    {
        $row = new ActionRow;

        foreach ($this->gameData[$game]->buttons as $button) {
            $row->addComponent($button);
        }

        return $row;
    }

    // /**
    //  *  Get GameInstance|false or bool if user is playing a specific or any game
    //  */
    // public function userIsPlaying(string $uid, ?string $game = null, bool $returnGameInstance = false): GameInstance|bool
    // {

    //     if ($returnGameInstance && $game === null) {
    //         throw new Exception('must specify game to return GameInstance');
    //     }

    //     if ($game === null) {
    //         foreach ($this->gameData as $data) {
    //             if ($data->owner === $uid) {
    //                 return true;
    //             }
    //         }

    //         return false;
    //     }

    //     foreach ($this->gameData as $game => $data) {
    //         if ($data->owner === $uid && $game === $data->gameType) {
    //             return $returnGameInstance ? $game : true;
    //         }
    //     }

    //     return false;
    // }

    /**
     * Handle timed events
     */
    public function checkTimedEvents(): void
    {
        foreach ($this->gameData as $game => $data) {

            if ($game instanceof TimedGameInstance) {
                $game->checkTimedInstance();
            }

            if ($game->expired()) {
                echo self::createUpdateMessage('', 'removed old game '.json_encode($data)), PHP_EOL;
                $this->games->detach($game);
                unset($game);
            }
        }
    }

    private function getFromId(string $id): ?GameInstance
    {
        foreach ($this->gameData as $game => $data) {
            if ($data->id === $id) {
                return $game;
            }
        }

        return null;
    }

    private function IdExists(string $id): bool
    {
        foreach ($this->gameData as $data) {
            if ($data->id === $id) {
                return true;
            }
        }

        return false;
    }
}

/**
 * make close all games command
 */
