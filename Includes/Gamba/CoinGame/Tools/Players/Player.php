<?php

declare(strict_types=1);

namespace Gamba\CoinGame\Tools\Players;

use Debug\Debug;
use Deprecated;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\User\User;
use Exception;
use Gamba\Loot\Item\Inventory;
use Gamba\Loot\Item\InventoryManager;
use Infrastructure\Exceptions\UndefinedProperty;
use InvalidArgumentException;
use LogicException;
use stdClass;
use Stringable;
use Tools\Discord\Text\Format;

/**
 * Represents a Discord user as a Player.
 */
final class Player implements Stringable
{
    use Debug;

    private const string DISCORD_AVATAR_URL = 'https://cdn.discordapp.com/avatars/';

    public readonly Inventory $inventory;

    /**
     * Store Player data in an stdClass.
     */
    public stdClass $data {
        get {
            if (! isset($this->playerData)) {
                $this->playerData = new stdClass();
            }

            return $this->playerData;
        }
    }

    /**
     * Id of the Discord user.
     */
    public private(set) string $uid {
        get {
            return $this->user->id;
        }

        set(string $id) {
            throw new LogicException('Cannot change a users id');
        }
    }

    /**
     * Players Discord user/display name.
     */
    public private(set) string $name {
        get {
            return $this->user->global_name ?? $this->user->username;
        }

        set(string $id) {
            throw new LogicException('Cannot change a users name');
        }
    }

    /**
     * Players Discord avatar as url.
     */
    public private(set) string $avatar {
        get {
            return self::DISCORD_AVATAR_URL.$this->uid.'/'.$this->user->avatar;
        }

        set(string $value) {
            throw new LogicException('Cannot change a users avatar url');
        }
    }

    private readonly User $user;

    private stdClass $playerData;

    /**
     * Create a Player Object
     *
     * @param  string|User  $player  Discord user id or User object.
     *
     * @throws InvalidArgumentException If:\
     *                                  #1 - The User is a bot.\
     *                                  #2 - A User could not be retrieved.\
     *                                  #3 - Passing user id without a Discord instance.
     */
    public function __construct(
        string|User $player,
        InventoryManager $inventoryManager,
        ?Discord $discord = null,
    ) {
        if ($player instanceof User) {
            $this->user = $player;
        } else {
            if (! $discord instanceof Discord) {
                throw new InvalidArgumentException('if $player is of type string an instance Discord must be passed', code: 3);
            }

            try {
                $discord->users->fetch($player)->then(fn (User $user): User => $this->user = $user);
            } catch (Exception $e) {
                throw new InvalidArgumentException('(from id: '.$player.') '.$e->getMessage(), code: 2, previous: $e);
            }
        }

        if ($this->user->bot === true) {
            throw new InvalidArgumentException('a bot cannot become a Player', code: 1);
        }

        $this->inventory = $inventoryManager->getInventory($this->uid);
    }

    /**
     * @return string Username or global name.
     */
    public function __toString(): string
    {
        return $this->name;
    }

    // (mby) make get and set go into $playerData.

    public function __get(string $name): never
    {
        throw new UndefinedProperty(
            message: self::createUpdateMessage('', 'property of '.$name.' does not exist on object of '.self::class)
        );
    }

    public function __set(string $name, mixed $value): void
    {
        echo self::createUpdateMessage('', 'property of '.$name.' does not exist on object of '.self::class);
    }

    /**
     * Static Player constructor
     */
    public static function new(
        string|User $player,
        InventoryManager $inventoryManager,
        Discord $discord,
    ): self {
        return new self($player, $inventoryManager, $discord);
    }

    /**
     * Send a message to the player
     */
    public function message(MessageBuilder $message): void
    {
        $this->user->sendMessage($message);
    }

    /**
     * Get a Discord mention of the player
     */
    public function getMention(): string
    {
        return Format::mention()->user($this->uid);
    }

    #[Deprecated(message: "'use the associated property instead'")]
    public function getName(): string
    {
        return $this->user->global_name ?? $this->user->username;
    }

    #[Deprecated(message: "'use the associated property instead'")]
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
