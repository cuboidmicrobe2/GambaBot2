<?php

namespace Gamba\Loot\Item;

use Gamba\Loot\Item\Inventory;
use Pdo\Mysql;

class InventoryManager { // mby dont need this (needs something to store pdo obj, so mby this?)

    private Mysql $conn;

    public function __construct(Mysql $conn) {
        $this->conn = $conn;
        $this->conn->setAttribute(Mysql::ATTR_ERRMODE, Mysql::ERRMODE_EXCEPTION);
    }

    public function getInventory(string $uid) : Inventory {
        return new Inventory($uid, $this->conn);
    }
}
