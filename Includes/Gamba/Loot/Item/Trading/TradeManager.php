<?php

declare(strict_types=1);

namespace Gamba\Loot\Item\Trading;

use Debug\Debug;
use Discord\Discord;
use Gamba\Loot\Item\ItemCollection;

final class TradeManager
{
    use Debug;

    private array $offers = [];

    public function createOffer(
        string $from,
        string $to,
        ItemCollection $formItems,
        ItemCollection $toItems
    ): bool {
        $key = self::createKey($from, $to);
        if (array_key_exists($key, $this->offers[$key])) {
            return false;
        } // offer exists

        $this->offers[$key] = new TradeOffer(
            from: $from,
            to: $to,
            formItems: $formItems,
            toItems: $toItems,
        );

        return true;
    }

    public function clean(Discord $discord): void
    {
        foreach ($this->offers as $key => $tradeOffer) {
            if ($tradeOffer->isOld()) {
                unset($tradeOffer[$key]);
                echo self::createUpdateMessage('', 'Removed trade offer '.$key), PHP_EOL;
            }
        }
    }

    private static function createKey(string $from, string $to): string
    {
        return $from.'->'.$to;
    }
}
