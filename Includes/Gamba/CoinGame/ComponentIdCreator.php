<?php

declare(strict_types=1);

namespace Gamba\CoinGame;

use Discord\Parts\Interactions\Interaction;
use Exception;

final class ComponentIdCreator
{
    private readonly string $id;

    private array $customIds;

    public function __construct(Interaction $interaction)
    {
        $this->id = $interaction->id;
    }

    public function createId(string $componentName): string
    {
        if (str_contains($componentName, ':')) {
            throw new Exception('component name cannot contain ":"');
        }
        $id = $this->id.':'.$componentName;
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
}
