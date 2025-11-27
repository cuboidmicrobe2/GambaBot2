<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class LogOnClose
{
    /**
     * @param  string  $message  Message to be logged.
     */
    public function __construct(public string $message) {}

    public function log(): void
    {
        echo $this->message;
    }
}
