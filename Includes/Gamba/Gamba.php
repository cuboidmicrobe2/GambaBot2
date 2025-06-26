<?php

namespace Gamba;

use DateTime;
use DateTimeZone;
use Discord\Builders\MessageBuilder;

use Gamba\CoinGame\Roulette\Roulette;
use Gamba\CoinGame\Roulette\Color;

use Gamba\Loot\Decide;
use Gamba\Loot\Item\Inventory;
use Gamba\Loot\Item\InventoryManager;
use Gamba\Loot\Item\ItemCollection;
use Gamba\Loot\Item\Item;
use Gamba\Loot\Rarity;
use OutOfRangeException;
use Pdo\Mysql;
use PDOStatement;

final class Gamba {
    
    private PDOStatement $fetchRandItem;
    private Mysql $gambaConn;

    public function __construct(Mysql $gambaConn, public private(set) InventoryManager $inventoryManager) {
        $this->gambaConn = $gambaConn;
        $this->fetchRandItem = $gambaConn->prepare(<<<SQL
            SELECT id, name 
            FROM items
            WHERE rarity = :rarity
            ORDER BY RAND()
            LIMIT 1;
        SQL);
    }

    public function getHistory(string $uid, int $amount) : ItemCollection {
        $result = $this->gambaConn->query(<<<SQL
            SELECT name, rarity, descr 
            FROM history 
            JOIN items 
            ON items.id = item_id 
            WHERE uid = {$uid}
            LIMIT {$amount};
        SQL);

        $items = new ItemCollection($amount);
        $i = 0;
        while($row = $result->fetch(Mysql::FETCH_ASSOC)) {
            $items[$i] = new Item(
                name: $row['name'],
                rarity: Rarity::tryFrom($row['rarity']),
                description: $row['descr']
            );
            $i++;
        }

        return $items;
    }

    public function roulette(string $uid, int $wager, int $bet, ?MessageBuilder &$message = null) : void {
        $userInventory = $this->inventoryManager->getInventory($uid);

        $coins = $userInventory->getCoins();

        if($coins < $wager) {
            $messageContent = match($coins) {
                0 => 'You do not have any coins! try '.COMMAND_LINK_DAILY,
                default => 'You do not have enough coins for that! (`'.$coins.'` coins)'
            };
            $message?->setContent($messageContent);
            return;
        } 
    
        $color = Color::getFromRoll(Roulette::roll());

        if($color->isMatch($bet)) {
            $winAmount = match($color) {
                Color::GREEN => $wager * 39,
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

    /**
     * @return array{coins:int, goldPity:int, purplePity:int}
     */
    public function getUserStats(string $uid) : array {
        $userInventory = $this->inventoryManager->getInventory($uid);

        return [
            'coins' => $userInventory->getCoins(),
            'goldPity' => $userInventory->getGoldPity(),
            'purplePity' => $userInventory->getPurplePity()
        ];
    }

    // public function getInventory(string $uid) : Inventory {
    //     return $this->inventoryManager->getInventory($uid);
    // }

    public function daily(string $uid, MessageBuilder &$message) : void {
        $userInventory = $this->inventoryManager->getInventory($uid);
        $today = new DateTime('now', new DateTimeZone(TIME_ZONE));

        $lastDaily = $userInventory->getLastDaily();
        if(date('Y-m-d', $lastDaily) === $today->format('Y-m-d')) {
            $tomorrow = $today->modify('+1 day');
            $nextReset = preg_replace('/[0-9]{2}(:[0-9]{2}){2}/', '00:00:00', $tomorrow->format('c'));
            $unix = strtotime($nextReset);
            $message->setContent("You have already claimed your daily coins. Next /daily <t:$unix:R>.");
            return;
        }

        $min = 500;
        $max = mt_rand($min, 1300);
        $amount = mt_rand($min, $max);
        $userInventory->setCoins($userInventory->getCoins() + $amount);
        $message->setContent("You got $amount coins.");
        $userInventory->updateDaily();
    }

}