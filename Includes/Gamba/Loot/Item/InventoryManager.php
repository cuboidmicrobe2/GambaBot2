<?php

declare(strict_types=1);

namespace Gamba\Loot\Item;

use Database\PersistentConnection;
use Debug\Debug;
use HTTP\Request;
use OutOfRangeException;
use Pdo\Mysql;
use WeakReference;

final class InventoryManager
{
    use Debug;
    private readonly PersistentConnection $conn;

    /**
     * @var array<string, WeakReference>
     */
    private array $inventoryCache = [];

    public function __construct(
        string $dsn,
        ?string $username = null,
        ?string $password = null,
        ?array $options = null,
    ) {
        $this->conn = PersistentConnection::connect('InventoryManager', $dsn, $username, $password, $options);
    }

    public function getInventory(string $uid): Inventory
    {
        $ref = $this->inventoryCache[$uid]?->get();

        if ($ref instanceof Inventory) {
            return $ref;
        }

        $inv = new Inventory($uid, $this->conn->getConnection());
        $this->inventoryCache[$uid] = WeakReference::create($inv);
        return $inv;
    }

    public function leaderboard(int $top): array
    {
        if ($top < 1) {
            throw new OutOfRangeException('leaderboard $top cannot be less than 1');
        }

        $result = $this->conn->getConnection()->query(<<<SQL
            SELECT uid, coins
            FROM user_stats
            ORDER BY coins DESC
            LIMIT {$top};
        SQL);

        $data = [];

        $requests = new Request();
        while ($row = $result->fetch(Mysql::FETCH_ASSOC)) {
            $data[]['coins'] = $row['coins'];

            $requests->bind('https://discord.com/api/v9/users/'.$row['uid'], [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bot '.$_ENV['DISCORD_TOKEN'],
                ],
            ]);
        }

        $requests->execute();

        $i = 0;
        foreach ($requests->fetch() as $userData) {
            $userInfo = json_decode($userData, true);
            $name = $userInfo['global_name'] ?? $userInfo['username'] ?? 'CURL_ERROR';
            $data[$i]['user'] = $name;
            $i++;
        }

        return $data;
    }

    public function clearChace(): void
    {
        foreach ($this->inventoryCache as $uid => $ref) {
            if ($ref instanceof WeakReference && $ref->get() === null) {
                unset($this->inventoryCache[$uid]);
            }
        }
    }
}
