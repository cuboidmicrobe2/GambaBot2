<?php

declare(strict_types = 1);

namespace Gamba\CoinGame\Games\ColorGame;

use Gamba\CoinGame\GameInstance;
use function GambaBot\Discord\TextStyle\code;

final class ColorGame extends GameInstance {

    private const float MULTIPLIER_MOD = 1.7;

    public private(set) array $guessHistory = [];
    
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

    public function __construct(public readonly int $wager) {
        parent::__construct();

        $this->winnings = $wager;
    }

    /**
     * @return array{win:bool, color:string}
     */
    public function guess(string $color) : array {
        $this->guessHistory[] = $color;
        $randColor = $this->colors[array_rand($this->colors)];
        if($color == $randColor) {
            $this->winnings *= $this->multiplier;
            $this->incrMultiplier();
            return ['win' => true, 'color' => $randColor];
        }
        
        $this->winnings = 0;
        return ['win' => false, 'color' => $randColor];
    }

    public function historyAsString() : string {
        // $last = array_key_last($this->guessHistory);
        // if($last === null) return 'No guesses';

        // if(array_key_first($this->guessHistory) == $last) return $this->guessHistory[$last];

        // $guessString = '';
        // foreach($this->guessHistory as $key => $guess) {
        //     if($key < $last) {
        //        $guessString .= $guess . ', ';
        //        continue;
        //     }
        //     $guessString .= 'and ' . $guess;
        //     return $guessString;
        // }

        // return 'No guesses';

        if(count($this->guessHistory) < 2) {
            return code($this->guessHistory[array_key_first($this->guessHistory)] ?? 'No guesses');
        }

        $last = array_key_last($this->guessHistory);
        $guessString = '';
        foreach($this->guessHistory as $key => $color) {
            if($key != $last) {
                $guessString .= code($color) . ' > ';
                continue;
            }

            $guessString .= code($color);
            return $guessString;
        }

        return code('something went wrong');
    }

    private function incrMultiplier() : void {
        $this->multiplier *= self::MULTIPLIER_MOD;
    }
}