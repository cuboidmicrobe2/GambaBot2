<?php

namespace Gamba\Loot\Item;

use PDO;

class Inventory {

    public function __construct(private string $owner, private PDO &$pdo) {}

    public function addItem(Item|int $item, int $count = 1) : void {
        $itemId = ($item instanceof Item) ? $item->id : $item;

        $this->pdo->query(<<<SQL
            INSERT INTO USER_{$this->owner} (item_id, count)
            VALUES ({$itemId}, {$count})
            ON DUPLICATE KEY UPDATE count = count + {$count}
        SQL);
    }

    public function addCollection(ItemCollection $items) : void {
        $stmt = $this->pdo->prepare(<<<SQL
            INSERT INTO USER_{$this->owner} (item_id, count)
            VALUES (:itemId, 1)
            ON DUPLICATE KEY UPDATE count = count + 1
        SQL);

        foreach($items as $item) {
            $stmt->execute(['itemId' => $item->id]);
        }
    }

    public function removeItem(Item|int $item, int $count = 1) : void {
        $itemId = ($item instanceof Item) ? $item->id : $item;

        $this->pdo->query(<<<SQL
            UPDATE USER_{$this->owner}
            SET count = count - {$count}
            WHERE item_id = {$itemId}
        SQL);
    }

    public function itemCount(int $itemId) : int {
        $result = $this->pdo->query(<<<SQL
            SELECT count FROM USER_{$this->owner}
            WHERE item_id = {$itemId};
        SQL);

        $result->setFetchMode(PDO::FETCH_ASSOC);

        // Fine since result can and sould only be one row
        return $result->fetch()['count'] ?? 0;
    }

    /**
     * @return array{unique:int, total:int}
     */
    public function size() : array {
        $result = $this->pdo->query(<<<SQL
            SELECT count FROM USER_{$this->owner};
        SQL);

        $result->setFetchMode(PDO::FETCH_ASSOC);

        $uniqueItems = 0;
        $totalItemCount = 0;

        foreach($result as $row) {
            if($row['count'] > 0) {
                $uniqueItems++;
                $totalItemCount += $row['count'];
            }
        }

        return ['unique' => $uniqueItems, 'total' => $totalItemCount];
    }
}