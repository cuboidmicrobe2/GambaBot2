<?php

declare(strict_types = 1);

namespace Gamba\CoinGame;

use Debug\Debug;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use Exception;
use InvalidArgumentException;
use JsonSerializable;
use React\Promise\PromiseInterface;

use function GambaBot\getUserId;

final class GameData implements JsonSerializable {
    use Debug;

    public readonly string $id;
    public readonly string $owner;

    public private(set) int $timeOfCreation;
    public private(set) string $gameType;
    private ?MessageBuilder $lastMessage = null;

    private ?array $buttons;

    private function __construct(private Interaction $interaction, ?ButtonCollection $buttons = null,) {

        $this->id = $this->interaction->id;
        $this->owner = getUserId($this->interaction);
        
        if($buttons) {
            foreach($buttons as $button) {
                $this->buttons[$button->getCustomId()] = $button; 
            }
        }

        $this->timeOfCreation = time();
    }

    public function __destruct() {
        $this->removeButtonListeners();
        // $lastContent = $this->lastMessage?->getContent() ?? ' ';
        $message = MessageBuilder::new()/*->setContent($lastContent)*/;
        $row = new ActionRow;
        // foreach($this->buttons as $button) {
        //     $row->addComponent($button);
        // }
        $row->addComponent(Button::secondary()->setLabel("\0")->setDisabled(true));
        $message->addComponent($row);
        $this->interaction->updateOriginalResponse($message);

    }

    public static function create(Interaction $interaction, ?ButtonCollection $buttons = null, ) : self {
        return new self($interaction, $buttons);
    }

    public function setType(string $type) : void {
        $this->gameType = $type;
    }

    public function addButton(Button $button) : void {
        $this->buttons[] = $button;
    }

    /**
     * @throws InvalidArgumentException Button does not exist
     */
    public function removeButton(string $id) : void {
        if(!isset($this->buttons[$id])) throw new InvalidArgumentException('Button with id: ' . $id . ' does not exist');
        $button = $this->buttons[$id];
        unset($this->buttons[$id]);
        $button->removeListener();
        echo self::createUpdateMessage('', 'removed button ' . $this->id . ' ' . $button->getCustomId()), PHP_EOL;
    }   

    public function removeButtonListeners() : void {
        foreach($this->buttons as $button) {
            $button->removeListener();
            $button->setDisabled(true);
            echo self::createUpdateMessage('', 'removed listener from ' . $this->id . ' ' . $button->getCustomId()), PHP_EOL;
        }
    }

    public function updateMessage(MessageBuilder $message) : PromiseInterface {
        $this->lastMessage = $message;
        return $this->interaction->updateOriginalResponse($message);
    }

    public function jsonSerialize() : array {
        return [
            'id' => $this->id,
            'owner' => $this->owner,
            'gameType' => $this->gameType,
            'timeOfCreation' => $this->timeOfCreation,
            'buttons' => count($this->buttons),
        ];
    }
}