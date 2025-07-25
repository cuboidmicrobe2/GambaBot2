<?php

declare(strict_types=1);

namespace Gamba\CoinGame;

use Discord\Builders\Components\Button;
use Infrastructure\SimpleArray;

final class ButtonCollection extends SimpleArray
{
    public function __construct(int $size)
    {
        parent::__construct(Button::class, $size);
    }
}
