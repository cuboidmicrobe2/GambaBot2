<?php

namespace Gamba;

use DateTime;
use DateTimeZone;
use Discord\Builders\MessageBuilder;

use Gamba\CoinGame\Roulette\Roulette;
use Gamba\CoinGame\Roulette\Color;

use Gamba\Loot\Decide;
use Gamba\Loot\Item\InventoryManager;
use Gamba\Loot\Item\ItemCollection;
use Gamba\Loot\Item\Item;

use Pdo\Mysql;
use PDOStatement;

final class Gamba {
    
    private PDOStatement $fetchRandItem;

    public function __construct(Mysql $itemConn, private InventoryManager $inventoryManager) {
        $this->fetchRandItem = $itemConn->prepare(<<<SQL
            SELECT id, name 
            FROM items
            WHERE rarity = :rarity
            ORDER BY RAND()
            LIMIT 1;
        SQL);
    }

    public function roulette(string $uid, int $wager, int $bet, ?MessageBuilder &$message = null) : void {
        $userInventory = $this->inventoryManager->getInventory($uid);

        $coins = $userInventory->getCoins();

        if($coins < $wager) {
            $message?->setContent('You do not have enough coins for that! ('.$coins.' coins)');
            return;
        } 
    
        $color = Color::getFromRoll(Roulette::roll());

        if($color->isMatch($bet)) {
            $winAmount = match($color) {
                Color::GREEN => $wager * 13,
                default => $wager * 2
            };

            $userInventory->setCoins($coins + $winAmount);
            $message?->setContent('You rolled ' . $color->name . ' and won ' . $winAmount . ' coins!');
            return;
        }
        else {
            $userInventory->setCoins($coins - $wager);
            $message?->setContent('You rolled ' . $color->name . ' and lost ' . $wager . ' coins!');
            return;
        }
    }

    public function wish(string $uid, int $rolls) : ItemCollection {
        $userInventory = $this->inventoryManager->getInventory($uid);

        $goldPity = $userInventory->getGoldPity();
        $purplePity = $userInventory->getPurplePity();

        $items = new ItemCollection($rolls);

        for($i = 0; $i < $rolls; $i++) {
            $itemRarity = Decide::rarity($goldPity, $purplePity);

            $this->fetchRandItem->execute(['rarity' => $itemRarity->value]);
            $result = $this->fetchRandItem->fetch(Mysql::FETCH_ASSOC);

            $items[$i] = new Item(
                name:   $result['name'],
                rarity: $itemRarity,
                id:     $result['id']
            );
        };

        $userInventory->setGoldPity($goldPity);
        $userInventory->setPurplePity($purplePity);
        $userInventory->addCollection($items);

        return $items;
    }

    public function daily(string $uid, MessageBuilder &$message) : void {
        $userInventory = $this->inventoryManager->getInventory($uid);
        $today = new DateTime('now', new DateTimeZone(TIME_ZONE));

        $lastDaily = $userInventory->getLastDaily();
        if(date('Y-m-d', $lastDaily) === $today->format('Y-m-d')) {
            $nextReset = $today->modify('+1 day')->format('U');
            $message->setContent("You have already claimed your daily coins. Next /daily <t:$nextReset:R>.");
            return;
        }

        $min = 1000;
        $max = mt_rand(1000, 2000);
        $amount = mt_rand($min, $max);
        $userInventory->setCoins($userInventory->getCoins() + $amount);
        $message->setContent("You got $amount coins.");
        $userInventory->updateDaily();
    }
}