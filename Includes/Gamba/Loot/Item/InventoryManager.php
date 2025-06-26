<?php

namespace Gamba\Loot\Item;

use CurlHandle;
use Gamba\Loot\Item\Inventory;
use HTTP\Request;
use Pdo\Mysql;
use OutOfRangeException;

class InventoryManager { // mby dont need this (needs something to store pdo obj, so mby this?)

    private Mysql $conn;

    public function __construct(Mysql $conn) {
        $this->conn = $conn;
        $this->conn->setAttribute(Mysql::ATTR_ERRMODE, Mysql::ERRMODE_EXCEPTION);
    }

    public function getInventory(string $uid) : Inventory {
        return new Inventory($uid, $this->conn);
    }

    public function leaderboard(int $top) : array {
        if($top < 1) throw new OutOfRangeException('leaderboard $top cannot be less than 1');

        $result = $this->conn->query(<<<SQL
            SELECT uid, coins
            FROM user_stats
            ORDER BY coins DESC
            LIMIT {$top};
        SQL);

        $data = [];

        $requests = new Request();
        while($row = $result->fetch(Mysql::FETCH_ASSOC)) {
            $data[]['coins'] = $row['coins'];

            $requests->bind('https://discord.com/api/v9/users/' . $row['uid'], [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bot ' . $_ENV['DISCORD_TOKEN']
                ]
            ]);
        }

        $requests->execute();

        $i = 0;
        foreach($requests->fetch() as $userData) {
            $userInfo = json_decode($userData, true);
            $name = isset($userInfo['global_name']) ? $userInfo['global_name'] : (isset($userInfo['username']) ? $userInfo['username'] : 'CURL_ERROR');
            $data[$i]['user'] = $name;
            $i++;
        }

        return $data;
    }
}
