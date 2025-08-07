<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\Components;

use InvalidArgumentException;
use JsonSerializable;

/**
 * @template TKey of string
 * @template TValue of string
 */
final class ComponentIdMap implements JsonSerializable
{
    /**
     * @var array<TKey, TValue>
     */
    private array $data = [];

    /**
     * @param TKey      $componentName
     * @param TValue    $componentId
     * @throws InvalidArgumentException TKey already exist
     */
    public function add(string $componentName, string $componentId): void
    {
        if ($this->idExists($componentName)) {
            throw new InvalidArgumentException('cannot override existing component');
        }

        $this->data[$componentName] = $componentId;
    }

    /**
     * @param TKey  $componentName
     * @throws InvalidArgumentException TKey does not exist
     */
    public function remove(string $componentName): void
    {
        $this->throwIfInvlaid($componentName);
        unset($this->data[$componentName]);
    }

    /**
     * @param TKey  $componentName
     * @return TValue
     * @throws InvalidArgumentException TKey does not exist
     */
    public function get(string $componentName): string
    {
        $this->throwIfInvlaid($componentName);
        return $this->data[$componentName];
    }

    /**
     * @param TKey  $componentName
     * @return TValue
     * @throws InvalidArgumentException TKey does not exist
     */
    public function getAndRemove(string $componentName): string
    {
        $this->throwIfInvlaid($componentName);

        $id = $this->data[$componentName];
        unset($this->data[$componentName]);

        return $id;
    }

    /**
     * @param TKey  $componentName
     */
    public function idExists(string $componentName): bool
    {
        return isset($this->data[$componentName]);
    }

    /**
     * @param TKey  $key
     * @throws InvalidArgumentException TKey does not exist
     */
    private function throwIfInvlaid(string $key): void
    {
        if (! $this->idExists($key)) {
            throw new InvalidArgumentException('TKey '.$key.' does not exist');
        }
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}