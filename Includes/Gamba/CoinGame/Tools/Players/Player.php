<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\Players;

use Deprecated;
use Discord\Discord;
use Discord\Parts\User\User;
use Gamba\Loot\Item\Inventory;
use Gamba\Loot\Item\InventoryManager;
use LogicException;
use stdClass;

final class Player
{
    private readonly User $user;
    public readonly Inventory $inventory;
    public stdClass $data;

    public private(set) string $uid {
        get {
            return $this->user->id;
        }

        set(string $id) {
            throw new LogicException('Cannot change a users id');
        }
    }

    public private(set) string $name {
        get {
            return $this->user->global_name ?? $this->user->username;
        }

        set(string $id) {
            throw new LogicException('Cannot change a users name');
        }
    }

    public function __construct(
        string $uid, 
        InventoryManager $inventoryManager,
        Discord $discord,
        // public ?array $data = null
        bool $initData
    ) {
        if ($initData) {
            $this->data = new stdClass;
        }

            $discord->users->fetch($uid)->then(fn (User $user) => $this->user = $user);
        $this->inventory = $inventoryManager->getInventory($uid);
    }

    public function initData(): void
    {
        if ($this->data === null) {
            $this->data = new stdClass;
        }
    }

    /** @deprecated 'use the associated property instead' */
    #[Deprecated('use the associated property instead')]
    public function getName(): string
    {
        return $this->user->global_name ?? $this->user->username;
    }

    /** @deprecated 'use the associated property instead' */
    #[Deprecated('use the associated property instead')]
    public function getId(): string
    {
        return $this->user->id;
    }
}

// object(Discord\Parts\User\User)#936 (2) {
//   ["attributes"]=>
//   array(9) {
//     ["id"]=>
//     string(18) "289102730046472192"
//     ["username"]=>
//     string(5) "kubis"
//     ["discriminator"]=>
//     string(1) "0"
//     ["global_name"]=>
//     string(5) "KuBis"
//     ["avatar"]=>
//     string(32) "1bbb551d8d9c0dcc4a2462fd41465b58"
//     ["bot"]=>
//     bool(false)
//     ["public_flags"]=>
//     int(4194368)
//     ["primary_guild"]=>
//     object(stdClass)#179 (4) {
//       ["tag"]=>
//       string(4) "WFCD"
//       ["identity_guild_id"]=>
//       string(17) "77176186148499456"
//       ["identity_enabled"]=>
//       bool(true)
//       ["badge"]=>
//       string(32) "b77980ae47597b59f50923edbad59ea9"
//     }
//     ["collectibles"]=>
//     NULL
//   }
//   ["created"]=>
//   bool(true)
// }