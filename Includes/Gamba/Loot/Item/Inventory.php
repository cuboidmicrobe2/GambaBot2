<?php

declare(strict_types=1);

namespace Gamba\Loot\Item;

use InvalidArgumentException;
use OutOfRangeException;
use PDO\Mysql;

final class Inventory
{
    public int $coins {
        get {
            return $this->getCoins();
        }
    }

    /**
     * @param string $owner Id of a user.
     */
    public function __construct(private readonly string $owner, private Mysql $database)
    {
        $this->userInventoryMustExist($owner, $database);
    }

    /**
     * @param  int<0, max>  $coins
     *
     * @throws OutOfRangeException if coins < 0
     */
    public function setCoins(int $coins): void
    {
        if ($coins < 0) {
            throw new OutOfRangeException();
        }
        $this->database->query(<<<SQL
            UPDATE user_stats
            set coins = {$coins}
            WHERE uid = {$this->owner};
        SQL);
    }

    public function getCoins(): int
    {
        $result = $this->database->query(<<<SQL
            SELECT coins 
            FROM user_stats
            WHERE uid = {$this->owner};
        SQL);

        return $result->fetch(Mysql::FETCH_ASSOC)['coins'] ?? 0;
    }

    /**
     * Add coins to user inventory
     * 
     * @param int<0, max> $coins    note: do not add a negative number (for safety)
     */
    public function addCoins(int $coins): void
    {
        if ($coins < 0) {
            throw new InvalidArgumentException('do not add a negative number to inventory (for safety). validate with getCoins and then use setCoins');
        }

        $this->database->query(<<<SQL
            UPDATE user_stats
            set coins = coins + {$coins}
            WHERE uid = {$this->owner};
        SQL);
    }

    public function addItem(Item|int $item, int $count = 1): void
    {
        $itemId = ($item instanceof Item) ? $item->id : $item;

        $this->database->query(<<<SQL
            INSERT INTO USER_{$this->owner} (item_id, count)
            VALUES ({$itemId}, {$count})
            ON DUPLICATE KEY 
                UPDATE count = count + {$count};
        SQL);
    }

    public function addCollection(ItemCollection $items): void
    {
        $stmt = $this->database->prepare(<<<SQL
            INSERT INTO USER_{$this->owner} (item_id, count)
            VALUES (:itemId, 1)
            ON DUPLICATE KEY 
                UPDATE count = count + 1
        SQL);

        foreach ($items as $item) {
            $stmt->execute(['itemId' => $item->id]);
        }
    }

    public function removeItem(Item|int $item, int $count = 1): void
    {
        $itemId = ($item instanceof Item) ? $item->id : $item;

        $this->database->query(<<<SQL
            UPDATE USER_{$this->owner}
            SET count = count - {$count}
            WHERE item_id = {$itemId}
        SQL);
    }

    public function getItemCount(int $itemId): int
    {
        $result = $this->database->query(<<<SQL
            SELECT count FROM USER_{$this->owner}
            WHERE item_id = {$itemId};
        SQL);

        // Fine since result can and sould only be one row
        return $result->fetch(Mysql::FETCH_ASSOC)['count'] ?? 0;
    }

    /**
     * @param  int<0, max>  $newPity
     *
     * @throws OutOfRangeException if $newPity < 0
     */
    public function setGoldPity(int $newPity): void
    {
        if ($newPity < 0) {
            throw new OutOfRangeException();
        }

        $this->database->query(<<<SQL
            UPDATE user_stats
            SET gold_pity = {$newPity}
            WHERE uid = {$this->owner};
        SQL);
    }

    /**
     * @throws OutOfRangeException if $newPity < 0
     */
    public function setPurplePity(int $newPity): void
    {
        if ($newPity < 0) {
            throw new OutOfRangeException();
        }

        $this->database->query(<<<SQL
            UPDATE user_stats
            SET purple_pity = {$newPity}
            WHERE uid = {$this->owner};
        SQL);
    }

    public function getGoldPity(): int
    {
        $result = $this->database->query(<<<SQL
            SELECT gold_pity FROM user_stats 
            WHERE uid = {$this->owner};
        SQL);

        return $result->fetch(Mysql::FETCH_ASSOC)['gold_pity'] ?? 0;
    }

    public function getPurplePity(): int
    {
        $result = $this->database->query(<<<SQL
            SELECT purple_pity FROM user_stats 
            WHERE uid = {$this->owner};
        SQL);

        return $result->fetch(Mysql::FETCH_ASSOC)['purple_pity'] ?? 0;
    }

    public function getLastDaily(): int
    {
        $result = $this->database->query(<<<SQL
            SELECT last_daily
            FROM user_stats
            WHERE uid = {$this->owner};
        SQL);

        return $result->fetch(Mysql::FETCH_ASSOC)['last_daily'] ?? 0;
    }

    public function updateDaily(): void
    {
        $time = time();
        $this->database->query(<<<SQL
            UPDATE user_stats
            SET last_daily = {$time}
            WHERE uid = {$this->owner}
        SQL);
    }

    /**
     * @return array{unique:int, total:int}
     */
    public function size(): array
    {
        $result = $this->database->query(<<<SQL
            SELECT count FROM USER_{$this->owner};
        SQL);

        $result->setFetchMode(Mysql::FETCH_ASSOC);

        $uniqueItems = 0;
        $totalItemCount = 0;

        foreach ($result as $row) {
            if ($row['count'] > 0) {
                $uniqueItems++;
                $totalItemCount += $row['count'];
            }
        }

        return ['unique' => $uniqueItems, 'total' => $totalItemCount];
    }

    /**
     * Create the user table if it does not exist
     */
    private function userInventoryMustExist(string $uid, Mysql $database): void
    {
        $database->query(<<<SQL
            CREATE TABLE IF NOT EXISTS USER_{$uid}(
                item_id TINYINT UNSIGNED PRIMARY KEY NOT NULL,
                count INT UNSIGNED NOT NULL DEFAULT 0
            );

            INSERT INTO user_stats (uid)
            VALUES ({$uid})
            ON DUPLICATE KEY 
                UPDATE uid = uid
        SQL);
    }
}
