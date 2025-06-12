<?php

namespace Gamba\Loot\Item;

use Gamba\Loot\Item\Inventory;
use PDO;

class InventoryManager { // mby dont need this (needs something to store pdo obj, so mby this?)

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getInventory(string $uid) : Inventory {
        return new Inventory($uid, $this->pdo);
    }
}
