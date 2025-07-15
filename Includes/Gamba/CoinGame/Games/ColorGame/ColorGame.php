<?php

declare(strict_types = 1);

namespace Gamba\CoinGame\Games\ColorGame;

use Gamba\CoinGame\GameInstance;

final class ColorGame extends GameInstance {

    private const int MULTIPLIER_MOD = 2;
    
    public float $winnings {
        get {
            return round($this->winnings);
        }
    }

    public private(set) float $multiplier = 1.8;

    private array $colors = [
        'red', // Button::danger
        'green', // Button::success
        //'blue', // Button::primary
    ];

    public function __construct(private readonly int $wager) {
        parent::__construct();

        $this->winnings = $wager;
    }

    /**
     * @return array{win:bool, color:string}
     */
    public function guess(string $color) : array {
        $randColor = $this->colors[array_rand($this->colors)];
        if($color == $randColor) {
            $this->winnings *= $this->multiplier;
            $this->incrMultiplier();
            return ['win' => true, 'color' => $randColor];
        }
        
        $this->winnings = 0;
        return ['win' => false, 'color' => $randColor];
    }

    private function incrMultiplier() : void {
        $this->multiplier *= self::MULTIPLIER_MOD;
    }
}