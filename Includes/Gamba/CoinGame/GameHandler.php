<?php

declare(strict_types=1);

namespace Gamba\CoinGame;

use Debug\Debug;
use Debug\MessageType;
use Deprecated;
use Discord\Builders\Components\ActionRow;
use Gamba\CoinGame\MultiInteractionLink;
use Gamba\CoinGame\Attributes\LogOnClose;
use Exception;
use InvalidArgumentException;
use TimedGameInstance;
use WeakMap;

use function GambaBot\Tools\getAttributes;
use function GambaBot\Tools\isUsing;

/**
 * 
 */
final class GameHandler
{
    use Debug;

    /**
     * If the **GameHandler** has any active games.
     */
    public bool $hasActiveGames {
        get {
            return count($this->games) > 0;
        }
    }

    // private SplObjectStorage $games;

    /**
     * @var array<string, GameInstance>
     */
    private array $games = [];

    /**
     * @var WeakMap<GameInstance, GameData|GameDataMap>
     */
    private WeakMap $gameData;

    public function __construct()
    {
        $this->gameData = new WeakMap; // data is stored in a WeakMap beacuse SplObjectSotrage uses SeekableIterator and prob wont work async but is needed to prevent obj from gc
    }

    /**
     * Add a game to the **GameHandler**.
     * 
     * @param GameInstance $game The game to add.
     * @param GameData|GameDataMap $data Data associated with the game.
     */
    public function addGame(GameInstance $game, GameData|GameDataMap $data): void
    {

        if ($this->IdExists($data->id)) {
            throw new Exception('a GameInstance with the id '.$data->id.' already exists');
        }

        if (! $data instanceof GameDataMap && isUsing($game, MultiInteractionLink::class)) {
            throw new InvalidArgumentException('Games with a MultiInteractionLink must pass a GameDataMap');
        }

        if ($data instanceof GameDataMap && ! isUsing($game, MultiInteractionLink::class)) {
            throw new InvalidArgumentException('Games without a MultiInteractionLink must not use a GameDataMap');
        }

        $this->games[$data->id] = $game;
        $data->setType($game::class);
        $this->gameData[$game] = $data;
    }

    public function closeGame(GameInstance|string $game): bool
    {
        $gameId = ($game instanceof GameInstance) ? $this->gameData[$game]->id : $game;

        if (isset($this->games[$gameId])) {
            echo self::createUpdateMessage('', 'removed game '.json_encode($this->gameData[$game])), PHP_EOL;

            $gameInstance = $this->games[$gameId];
            $attrList = getAttributes($gameInstance);
            if (isset($attrList[LogOnClose::class])) {
                $attrList[LogOnClose::class]->newInstance()->log();
            }

            unset($this->games[$gameId]);
            return true;
        }
        
        echo self::createUpdateMessage('', 'could not find game: '.$gameId, MessageType::WARNING), PHP_EOL;
        return false;
    }

    #[\NoDiscard]
    public function getGame(string $interactionId): ?GameInstance
    {
        return $this->games[$interactionId] ?? null;
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

    #[\NoDiscard]
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

    #[\NoDiscard]
    public function getNewActionRow(GameInstance $game): ActionRow
    {
        $row = new ActionRow;

        foreach ($this->gameData[$game]->buttons as $button) {
            $row->addComponent($button);
        }

        return $row;
    }

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
                unset($this->games[$data->id]);
            }
        }
    }

    /**
     * @deprecated use GameHandler::getGame()
     */
    #[\Deprecated('use GameHandler::getGame()')]
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

    public function dump(): void
    {
        var_dump($this->games);
        var_dump($this->gameData);
    }
}

/**
 * make close all games command
 */
