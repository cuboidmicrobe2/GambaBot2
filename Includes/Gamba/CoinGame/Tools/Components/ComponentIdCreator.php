<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\Components;

use Discord\Parts\Interactions\Interaction;

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

    public function __construct(Interaction $interaction)
    {
        $this->id = $interaction->id;
    }

    /**
     * @param TKey $componentName
     */
    public function createId(string $componentName): string
    {
        // $id = $this->id.'/'.$componentName.'/'.hrtime(true);
        $id = 'button\\'.$componentName.'\\'.$this->id.'\\'.hrtime(true);
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
