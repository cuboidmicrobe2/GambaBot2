<?php

declare(strict_types=1);

namespace Tools\Discord\Text;

interface MentionTypeInterface
{
    public function user(string|int $userId): string;
    public function channel(string|int $channelId): string;
}