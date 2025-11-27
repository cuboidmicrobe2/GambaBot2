<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\Components;

use Discord\Parts\Interactions\ApplicationCommand;

/**
 * @template TKey of string
 * @template TValue of string
 */
final class ComponentIdCreator
{
    private readonly string $id;

    /**
     * @var array<TKey, TValue>
     */
    private array $customIds;

    public function __construct(ApplicationCommand $interaction)
    {
        $this->id = $interaction->id;
    }

    /**
     * @param  TKey  $componentName
     */
    public function createId(string $componentName, ComponentType $componentType): string
    {
        $id = $componentType->value.'\\'.$componentName.'\\'.$this->id.'\\'.hrtime(true);
        $this->customIds[$componentName] = $id;

        return $id;
    }

    public function getId(string $componentName): ?string
    {
        return $this->customIds[$componentName] ?? null;
    }

    public function getAllCustom(): array
    {
        return $this->customIds;
    }

    public function exportIdMap(): ComponentIdMap
    {
        $map = new ComponentIdMap;

        foreach ($this->customIds as $key => $value) {
            $map->add($key, $value);
        }

        return $map;
    }
}
