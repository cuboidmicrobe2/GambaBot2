<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class LogOnClose
{   
    /**
     * @param string $message Message to be logged.
     */
    public function __construct(public readonly string $message) {}

    public function log(): void
    {
        echo $this->message;
    }
}