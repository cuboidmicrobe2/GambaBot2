<?php

declare(strict_types=1);

namespace Gamba\Loot\Item;

use HTTP\Request;
use OutOfRangeException;
use Pdo\Mysql;

final readonly class InventoryManager
{ public function __construct(private Mysql $conn)
    {
        $this->conn->setAttribute(Mysql::ATTR_ERRMODE, Mysql::ERRMODE_EXCEPTION);
    }

    public function getInventory(string $uid): Inventory
    {
        return new Inventory($uid, $this->conn);
    }

    public function leaderboard(int $top): array
    {
        if ($top < 1) {
            throw new OutOfRangeException('leaderboard $top cannot be less than 1');
        }

        $result = $this->conn->query(<<<SQL
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
}
