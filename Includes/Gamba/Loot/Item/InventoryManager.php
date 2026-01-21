<?php

declare(strict_types=1);

namespace Gamba\Loot\Item;

use Database\PersistentConnection;
use Debug\Debug;
use Generator;
use HTTP\Request;
use Infrastructure\ObjectCache;
use NoDiscard;
use OutOfRangeException;
use Pdo\Mysql;

final class InventoryManager
{
    use Debug;

    /**
     * True if any **Inventory** is in use.
     */
    public bool $activeInventories {
        get {
            return $this->inventoryCache->countValid() > 0;
        }
    }

    private readonly PersistentConnection $conn;

    /**
     * @var ObjectCache<string, Inventory>
     */
    private ObjectCache $inventoryCache;

    /**
     * Constructor
     *
     * @param string $dsn
     * @param string|null $username
     * @param string|null $password
     * @param array<string, mixed>|null $options
     */
    public function __construct(
        string $dsn,
        ?string $username = null,
        ?string $password = null,
        ?array $options = null,
    ) {
        $this->conn = PersistentConnection::connect('InventoryManager', $dsn, $username, $password, $options);
        $this->inventoryCache = new ObjectCache;
    }

    /**
     * Get an **Inventory** representing a discord user.
     *
     * @param  string  $uid  The users discord id.
     */
    #[NoDiscard]
    public function getInventory(string $uid): Inventory
    {
        $ref = $this->inventoryCache->get($uid);

        if ($ref instanceof Inventory) {
            return $ref;
        }

        $inv = new Inventory($uid, $this->conn->getConnection());
        $this->inventoryCache->set($uid, $inv);

        return $inv;
    }

    /**
     * Get a leaderboard.
     *
     * @param int $top
     * @return array<int, array<string, mixed>>
     */
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

        if ($result === false) {
            return [];
        }

        /** @var array<int, array<string, int|string>> */
        $data = [];

        $requests = new Request();

        /** @var string */
        $botToken = $_ENV['DISCORD_TOKEN'];

        
        while ($row = $result->fetch(Mysql::FETCH_ASSOC)) {
            /** @var array<string, int|string> $row */

            $data[] = ['coins' => $row['coins']];

            $requests->bind('https://discord.com/api/v9/users/'.$row['uid'], [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bot '.$botToken,
                ],
            ]);
        }

        $requests->execute();

        $result = $requests->fetch();

        $i = 0;
        if ($result instanceof Generator) {
            foreach ($result as $userData) {
                if (is_string($userData)) {
                    /** @var array<string, mixed> */
                    $userInfo = json_decode($userData, true);
                    $name = $userInfo['global_name'] ?? $userInfo['username'] ?? 'CURL_ERROR';
                    $data[$i]['user'] = $name;
                    $i++; 
                }
            }
        }

        return $data;
    }

    /**
     * Clear the Inventory cache from old data
     */
    public function clearChace(): void
    {
        $this->inventoryCache->clean();
    }

    public function dump(): void
    {
        var_dump($this->inventoryCache);
    }
}
