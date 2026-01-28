<?php

declare(strict_types=1);

use Gamba\CoinGame\GameInstance;

/**
 * An extension of the **GameInstance** class that can saves the renew history.
 */
abstract class LoggingGameInstance extends GameInstance {
    /**
     * Renew history.
     *
     * @var array<int, array{uid: string, time: int}>
     */
    final protected array $_interactionHistory = [];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Renew the **GameInstance** and save it to $_interactionHistory.
     *
     * @param string $uid Discord id of the player who triggered the action.
     * @return void
     */
    final public function renewAndLog(string $uid): void
    {
        $this->_interactionHistory[] = ['uid' => $uid, 'time' => time()];
        $this->renew();
    }
}