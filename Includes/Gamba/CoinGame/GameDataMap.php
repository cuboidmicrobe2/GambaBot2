<?php

declare(strict_types=1);

namespace Gamba\CoinGame;

use InvalidArgumentException;

final class GameDataMap
{
    /**
     * Id of first added **GameData** object.
     */
    public readonly string $id;

    /**
     * @var array<string, GameData>
     */
    private array $data = [];

    public function add(GameData $data): void
    {
        if (! isset($this->id)) {
            $this->id = $data->id;
        }
        $this->data[$data->id] = $data;
    }

    /**
     * Get a **GameData** object linked to an id.
     *
     * @param  string  $id  Interaction id.
     *
     * @throws InvalidArgumentException If the id does not exist.
     */
    public function get(string $id): GameData
    {
        if (! isset($this->data[$id])) {
            throw new InvalidArgumentException($id.' does not exists');
        }

        return $this->data[$id];
    }
}
