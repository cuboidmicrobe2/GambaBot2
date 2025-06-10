<?php

namespace Gamba\Loot\Item;

use Gamba\Loot\Item\Inventory;
use PDO;

class InventoryManager {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getInventory(string $uid) : Inventory {
        self::userMustExist($uid, $this->pdo);

        return new Inventory($uid, $this->pdo);
    }
    
    /**
     * Create the user table if it does not exist
     */
    private static function userMustExist(string $uid, PDO $pdo) : void {
        $pdo->query(<<<SQL
            CREATE TABLE IF NOT EXISTS USER_$uid(
                item_id MEDIUMINT PRIMARY KEY NOT NULL,
                count INT NOT NULL DEFAULT 0
            );
        SQL);
    }
}
