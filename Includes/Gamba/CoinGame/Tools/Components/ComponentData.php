<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\Components;

use Discord\Builders\Components\Button;
use Discord\Builders\Components\ComponentObject;
use JsonSerializable;

readonly class ComponentData implements JsonSerializable
{
    public function __construct(
        public string $name,
        public string $id,
        public ComponentType $type,
        public int $timeOfCreation,
    ) {}

    /**
     * Create a new button with the same id
     */
    public function recreate(int $style): ComponentObject
    {
        $id = $this->type->value.'\\'.$this->name.'\\'.$this->id.'\\'.$this->timeOfCreation;
        return new Button($style, $id);
    }

    public static function fromJson(string $json): self
    {

    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}