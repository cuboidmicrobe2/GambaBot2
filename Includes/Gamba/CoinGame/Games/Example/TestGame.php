<?php

namespace Gamba\CoinGame\Games\Example;

use Gamba\CoinGame\GameInstance;

/**
 * Minimum game components
 */
final class TestGame extends GameInstance {

    private int $itemIterator = 0;

    private array $content = [
        'very',
        'cool',
        'stuff',
        'in cool',
        'array',
    ];

    public function __construct() {
        parent::__construct();

        $this->_lifeTime = 20;
    }

    public function getNext() : string {
        $this->renew();

        if(!isset($this->content[$this->itemIterator])) $this->itemIterator = 0;
        $item = $this->content[$this->itemIterator];
        $this->itemIterator++;
        return $item;
    }
}