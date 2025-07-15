<?php

declare(strict_types = 1);

namespace Gamba\CoinGame;

use Debug\Debug;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use JsonSerializable;
use React\Promise\PromiseInterface;

final class GameData implements JsonSerializable {
    use Debug;

    public private(set) int $timeOfCreation;

    public private(set) string $gameType;

    private ?MessageBuilder $lastMessage = null;

    private array $buttons;

    private function __construct(
        public private(set) string $id, 
        public private(set) string $owner, 
        public readonly Interaction $interaction,
        ?ButtonCollection $buttons = null,
    ) {
        $this->timeOfCreation = time();
        if($buttons) {
            foreach($buttons as $button) {
                $this->buttons[$button->getCustomId()] = $button; 
            }
        }
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

    public static function create(string $id, string $owner, ?ButtonCollection $buttons = null, Interaction $interaction) : self {
        $s = new self($id, $owner, $interaction, $buttons);
        return $s;
    }

    public function setType(string $type) : void {
        $this->gameType = $type;
    }

    public function addButton(Button $button) : void {
        $this->buttons[] = $button;
    }

    public function removeButtonListeners() : void {
        foreach($this->buttons as $button) {
            $button->removeListener();
            $button->setDisabled(true);
            echo self::createUpdateMessage('', 'removed listener from ' . $button->getCustomId()), PHP_EOL;
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
        ];
    }
}