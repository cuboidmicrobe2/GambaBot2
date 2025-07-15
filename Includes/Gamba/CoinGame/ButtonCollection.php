<?php

declare(strict_types = 1);

namespace Gamba\CoinGame;

use Infrastructure\SimpleArray;
use Discord\Builders\Components\Button;

final class ButtonCollection extends SimpleArray {
    public function __construct(int $size) {
        parent::__construct(Button::class, $size);
    }
}