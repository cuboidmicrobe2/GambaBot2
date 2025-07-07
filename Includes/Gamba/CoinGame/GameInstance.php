<?php

namespace Gamba\CoinGame;

abstract class GameInstance {

    protected int $_age;
    protected int $_lifeTime = 600;

    protected function __construct() {
        $this->_age = time();
    }

    /**
     * Returns a bool whether the objects has existed for longer than its intended lifetime and will be deleted when the GameHalndler runs clean()
     */
    public function expired() : bool {
        return (time() - $this->_age >= $this->_lifeTime);
    }

    /**
     * Update the age of the object to reset its life time
     */
    public function renew() : void {
        $this->_age = time();
    }
}