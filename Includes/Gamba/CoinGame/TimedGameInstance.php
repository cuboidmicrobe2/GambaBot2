<?php

declare(strict_types=1);

use Gamba\CoinGame\GameInstance;

abstract class TimedGameInstance extends GameInstance
{
    private readonly int $_gameEndTime;

    /**
     * @var callable
     */
    private $_timedAction;

    public function __construct(int $durationSec, callable $onFullDuration)
    {
        if ($durationSec < 10) {
            throw new InvalidArgumentException('durationSec cannot be less than 10 seconds');
        }

        $this->_gameEndTime = time() + $durationSec;
        $this->_timedAction = $onFullDuration;

        $durationX2 = $durationSec * 2;

        if ($durationX2 > $this->_lifeTime) {
            $this->_lifeTime = $durationX2;
        }

        parent::__construct();
    }

    final public function checkTimedInstance(): bool
    {
        if (time() > $this->_gameEndTime) {
            call_user_func($this->_timedAction);

            return true;
        }

        return false;
    }
}
