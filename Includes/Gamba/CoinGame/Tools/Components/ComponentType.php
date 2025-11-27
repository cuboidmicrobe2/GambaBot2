<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\Components;

/**
 * Type of component
 */
enum ComponentType: string
{
    case BUTTON = 'button';
    case TEXT_INPUT = 'text_input';
    case SELECT_USER = 'u-select';
}
