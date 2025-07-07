<?php

namespace Gamba\CoinGame\Games\Example;

use Gamba\CoinGame\GameInstance;

/**
 * Minimum game components
 */
final class TestGame extends GameInstance {
    public function __construct() {
        parent::__construct();

        $this->_lifeTime = 10;
    }
}


$a = new TestGame();

$a->renew();