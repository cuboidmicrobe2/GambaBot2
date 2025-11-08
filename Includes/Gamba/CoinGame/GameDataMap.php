<?php

declare(strict_types=1);

namespace Gamba\CoinGame;

use InvalidArgumentException;

final class GameDataMap
{
    /**
     * @var array<string, GameData>
     */
    private array $data = [];

    public function add(GameData $data): void
    {
        $this->data[$data->id] = $data;
    }

    public function get(string $id): GameData
    {
        if (! isset($this->data[$id])) {
            throw new InvalidArgumentException($id.' does not exists');
        }
        return $this->data[$id];
    }
}