<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\Components\Factories;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Parts\Interactions\ApplicationCommand;
use Gamba\CoinGame\Tools\Components\ButtonCollection;
use Gamba\CoinGame\Tools\Components\ComponentIdCreator;
use Gamba\CoinGame\Tools\Components\ComponentIdMap;
use Gamba\CoinGame\Tools\Components\ComponentType;

final class ButtonFactory
{
    private ComponentIdCreator $idCreator;

    /**
     * @var array<int, Button>
     */
    private array $buttons = [];

    public function __construct(private readonly ApplicationCommand $interaction)
    {
        $this->idCreator = new ComponentIdCreator($interaction);
    }
    
    public function create(int $style, string $name): Button
    {
        $button = Button::new($style, $this->idCreator->createId($name, ComponentType::BUTTON));
        $this->buttons[] = $button;
        return $button;
    }

    public function getMap(): ComponentIdMap
    {
        return $this->idCreator->exportIdMap();
    }

    public function createCollection(): ButtonCollection
    {
        $buttonCollection = new ButtonCollection(count($this->buttons));
        $buttonCollection->insert(...$this->buttons);
        return $buttonCollection;
    }

    public function createActionRow(): ActionRow
    {
        $row = new ActionRow();
        foreach ($this->buttons as $button) {
            $row->addComponent($button);
        }
        return $row;
    }
}