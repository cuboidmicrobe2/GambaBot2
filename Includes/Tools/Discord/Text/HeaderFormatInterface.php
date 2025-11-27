<?php

declare(strict_types=1);

namespace Tools\Discord\Text;

interface HeaderFormatInterface
{
    /**
     * Create a full-size header.
     */
    public function big(string $header): string;

    /**
     * Create a medium header.
     */
    public function medium(string $header): string;

    /**
     * Create the smallest header.
     */
    public function small(string $header): string;

    /**
     * Create subtext.
     */
    public function subtext(string $subtext): string;
}
