<?php

declare(strict_types=1);

use Gamba\Loot\Rarity;

$rootDir = str_replace('\Includes\Gamba\Loot\Item', '', __DIR__);
require_once $rootDir.'/Includes\Gamba\Loot\Rarity.php';

$id = 1;

$itemList = [
    [
        'id' => $id++,
        'name' => 'Revenant',
        'rarity' => Rarity::PURPLE->value,
        'description' => <<<'DESC'
        Doll of the daughter of an aristocratic family that faced ruin. On a fateful night, her soul took possession of the doll's body, and she swore to avenge her disgraced lineage.
        While steeped in faith, her other attributes develop at only a modest pace. Plays a supportive role in combat by summoning and manipulating spirits, which have the potential to overwhelm enemies.
        DESC
    ],
    [
        'id' => $id++,
        'name' => 'Crowbar',
        'rarity' => Rarity::BLUE->value,
        'description' => <<<'DESC'
        "Hey, toss me a crowbar?"
        DESC
    ],
    [
        'id' => $id++,
        'name' => '57 Leaf Clover',
        'rarity' => Rarity::GOLD->value,
        'description' => <<<'DESC'
        Or were they just lucky?
        DESC
    ],
    [
        'id' => $id++,
        'name' => 'Bloodbath',
        'rarity' => Rarity::GOLD->value,
        'description' => <<<'DESC'
        Whose blood will be spilt in the Bloodbath? Who will the victors be? How many will survive? Good luck...
        DESC
    ],
    [
        'id' => $id++,
        'name' => 'Rock',
        'rarity' => Rarity::BLUE->value,
        'description' => <<<'DESC'
        Maybe if you throw it hard enough...
        DESC
    ],
    [
        'id' => $id++,
        'name' => 'Throwing Dagger',
        'rarity' => Rarity::BLUE->value,
        'description' => <<<'DESC'
        Short dagger for throwing. It has no handguard.
        The blade is polished, and its weight is expertly balanced.
        DESC
    ],
    [
        'id' => $id++,
        'name' => 'Cinquedea',
        'rarity' => Rarity::PURPLE->value,
        'description' => <<<'DESC'
        The design celebrates a beast's five fingers, symbolic of the intelligence once granted upon their kind.
        DESC
    ],
    [
        'id' => $id++,
        'name' => 'Star-Lined Sword',
        'rarity' => Rarity::BLUE->value,
        'description' => <<<'DESC'
        Sword encrusted with a line of stars fashioned from small pieces of crude glintstone.
        When bestowed with this weapon by their queen, the swordsmen swear to find the truth that lies at the end of the procession of stars.
        DESC
    ],
    [
        'id' => $id++,
        'name' => 'Galatine',
        'rarity' => Rarity::BLUE->value,
        'description' => <<<'DESC'
        A sword this big cannot possibly be effective?
        DESC
    ],
    [
        'id' => $id++,
        'name' => 'Galatine Prime',
        'rarity' => Rarity::GOLD->value,
        'description' => <<<'DESC'
        Like it's none prime counterpart... with a bit more gold.
        DESC
    ],
    [
        'id' => $id++,
        'name' => 'Fragor',
        'rarity' => Rarity::BLUE->value,
        'description' => <<<'DESC'
        A large two-handed hammer, the Fragor requires great strength to wield, but impacts with enough force to send groups of enemies tumbling.
        DESC
    ],
    [
        'id' => $id++,
        'name' => 'Stug',
        'rarity' => Rarity::BLUE->value,
        'description' => <<<'DESC'
        Firing a sticky, toxic, explosive compound, the Stug Gel Gun offers multiple ejection modes, delivering maximum damage in all situations.
        DESC
    ],
    [
        'id' => $id++,
        'name' => 'Brother Corhyn',
        'rarity' => Rarity::BLUE->value,
        'description' => <<<'DESC'
        "Welcome to the Roundtable Hold. I'm Corhyn, a man of the cloth."
        DESC
    ],
    [
        'id' => $id++,
        'name' => 'Mask of the Quiet One',
        'rarity' => Rarity::GOLD->value,
        'description' => <<<'DESC'
        "..."
        DESC
    ],
];

if (count($itemList) > 255) {
    throw new Exception('Max item count is 255');
}

return $itemList;
