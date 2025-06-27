<?php
use Gamba\Loot\Rarity;

$rootDir = str_replace('\Includes\Gamba\Loot\Item', '', __DIR__);
require_once $rootDir.'/Includes\Gamba\Loot\Rarity.php';

$id = 1;

$itemList = [
    [   
        'id' => $id++,
        'name' => 'Revenant',
        'rarity' => Rarity::PURPLE->value,
        'description' => <<<DESC
        Doll of the daughter of an aristocratic family that faced ruin. On a fateful night, her soul took possession of the doll's body, and she swore to avenge her disgraced lineage.
        While steeped in faith, her other attributes develop at only a modest pace. Plays a supportive role in combat by summoning and manipulating spirits, which have the potential to overwhelm enemies.
        DESC 
    ],
    [
        'id' => $id++,
        'name' => 'Crowbar',
        'rarity' => Rarity::BLUE->value,
        'description' => <<<DESC
        "Hey, toss me a crowbar?"
        DESC 
    ],
    [
        'id' => $id++,
        'name' => '57 Leaf Clover',
        'rarity' => Rarity::GOLD->value,
        'description' => <<<DESC
        Or were they just lucky?
        DESC 
    ],
    [
        'id' => $id++,
        'name' => 'Bloodbath',
        'rarity' => Rarity::GOLD->value,
        'description' => <<<DESC
        Whose blood will be spilt in the Bloodbath? Who will the victors be? How many will survive? Good luck...
        DESC 
    ],
];

return $itemList;