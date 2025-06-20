<?php

namespace Gamba;

use Gamba\Loot\Decide;
use Gamba\Loot\Item\InventoryManager;
use Gamba\Loot\Item\ItemCollection;
use Gamba\Loot\Item\Item;
use Pdo\Mysql;
use PDOStatement;

final class Gamba {
    
    private PDOStatement $fetchRandItem;

    public function __construct(Mysql $itemConn) {
        $this->fetchRandItem = $itemConn->prepare(<<<SQL
            SELECT id, name 
            FROM items
            WHERE rarity = :rarity
            ORDER BY RAND()
            LIMIT 1;
        SQL);
    }

    public function __invoke(string $uid, int $rolls, InventoryManager $inventoryManager) : ItemCollection {
        $userInventory = $inventoryManager->getInventory($uid);

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
}