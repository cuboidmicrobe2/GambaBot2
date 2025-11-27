<?php

declare(strict_types=1);

namespace Gamba\CoinGame;

use Debug\Debug;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\ApplicationCommand;
use Exception;
use Gamba\CoinGame\Tools\Components\ButtonCollection;
use Gamba\CoinGame\Tools\Components\ComponentData;
use Gamba\CoinGame\Tools\Components\ComponentIdMap;
use Gamba\CoinGame\Tools\Components\ComponentType;
use InvalidArgumentException;
use JsonSerializable;
use React\Promise\PromiseInterface;
use Stringable;

use function GambaBot\Interaction\getUserId;

final class GameData implements JsonSerializable, Stringable
{
    use Debug;

    public readonly string $id;

    public readonly string $owner;

    public private(set) int $timeOfCreation;

    public private(set) string $gameType;

    /**
     * @var null|array<string, Button>
     */
    public private(set) ?array $buttons;

    private ?MessageBuilder $lastMessage = null;

    private function __construct(
        private ApplicationCommand $interaction,
        ?ButtonCollection $buttons = null,
        private ?ComponentIdMap $idMap = null,
        public ?array $data = null
    ) {

        $this->id = $this->interaction->id;
        $this->owner = getUserId($this->interaction);

        if ($buttons instanceof ButtonCollection) {
            if (! $idMap instanceof ComponentIdMap) {
                throw new Exception('missing ComponentIdMap');
            }

            foreach ($buttons->yield() as $button) {
                $this->buttons[$button->getCustomId()] = $button;
            }
        }

        $this->timeOfCreation = time();
    }

    public function __destruct()
    {
        $this->removeButtonListeners();
        // $lastContent = $this->lastMessage?->getContent() ?? ' ';
        $message = MessageBuilder::new()/* ->setContent($lastContent) */;
        $row = new ActionRow;
        // foreach($this->buttons as $button) {
        //     $row->addComponent($button);
        // }
        $row->addComponent(Button::secondary()->setLabel("\0")->setDisabled(true));
        $message->addComponent($row);
        $this->interaction->updateOriginalResponse($message);

    }

    public function __toString(): string
    {
        return self::class.'<'.$this->gameType.', '.$this->owner.'>';
    }

    public static function create(ApplicationCommand $interaction, ?ButtonCollection $buttons = null, ?ComponentIdMap $idMap = null, ?array $data = null): self
    {
        return new self($interaction, $buttons, $idMap, $data);
    }

    public function setType(string $type): void
    {
        $this->gameType = $type;
    }

    /**
     * Button id and name must already exist in the ComponentIdMap
     */
    public function addButton(Button $button): void
    {
        $this->buttons[] = $button;
    }

    public function getButtonId(string $componentName): string
    {
        return $this->idMap->get($componentName);
    }

    /**
     * @throws InvalidArgumentException Button does not exist
     */
    public function removeButton(string $componentName): void
    {
        $id = $this->idMap->getAndRemove($componentName);

        $button = $this->buttons[$id];
        unset($this->buttons[$id]);
        $button->removeListener();

        echo self::createUpdateMessage('', 'removed button '.$this->id.' '.$button->getCustomId()), PHP_EOL;
    }

    public function removeButtonListeners(): void
    {
        foreach ($this->buttons as $button) {
            $button->removeListener();
            $button->setDisabled(true);
            echo self::createUpdateMessage('', 'removed '.$button->getCustomId()), PHP_EOL;
        }
    }

    public function setButtonDisabledState(string $buttonName, bool $disabled): void
    {
        $this->buttons[$this->idMap->get($buttonName)]->setDisabled($disabled);
    }

    /**
     * @param  array<string, bool>  $states  [buttonName => bool]
     */
    public function setButtonDisabledStateArray(array $states): void
    {
        foreach ($states as $buttonName => $state) {
            $this->setButtonDisabledState($buttonName, $state);
        }
    }

    public function updateMessage(MessageBuilder $message): PromiseInterface
    {
        $this->lastMessage = $message;

        return $this->interaction->updateOriginalResponse($message);
    }

    public function getComponentData(string $componentName): ComponentData
    {
        $id = $this->idMap->get($componentName);

        $parts = explode('\\', $id);

        return new ComponentData(
            name: $parts[1],
            id: $parts[2],
            type: ComponentType::tryFrom($parts[0]),
            timeOfCreation: (int) $parts[3],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'owner' => $this->owner,
            'gameType' => $this->gameType,
            'timeOfCreation' => $this->timeOfCreation,
            'buttons' => count($this->buttons),
        ];
    }
}
