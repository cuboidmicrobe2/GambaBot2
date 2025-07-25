<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

$rootDir = str_replace('\Includes\Gamba', '', __DIR__);
require_once $rootDir.'/vendor/autoload.php';

$dotenv = new Dotenv;
$dotenv->load($rootDir.'/.env');

$tempDB = PDO::connect('mysql:host='.$_ENV['DB_HOSTNAME'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
$tempDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$tempDB->query(<<<'SQL'
    CREATE DATABASE IF NOT EXISTS gamba;
    CREATE DATABASE IF NOT EXISTS gamba_inventories;
    USE gamba;

    CREATE TABLE IF NOT EXISTS items (
        id TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(64) NOT NULL,
        rarity TINYINT NOT NULL,
        descr VARCHAR(256) DEFAULT ""
    );

    CREATE TABLE IF NOT EXISTS history (
        id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        item_id TINYINT UNSIGNED,
        uid BIGINT UNSIGNED
    );

    USE gamba_inventories;

    CREATE TABLE IF NOT EXISTS user_stats (
        uid BIGINT UNSIGNED PRIMARY KEY NOT NULL,
        coins BIGINT UNSIGNED DEFAULT 0,
        purple_pity SMALLINT UNSIGNED DEFAULT 0,
        gold_pity SMALLINT UNSIGNED DEFAULT 0,
        last_daily BIGINT UNSIGNED DEFAULT 0
    );
    
    USE gamba;
SQL);

$stmt = $tempDB->prepare(<<<'SQL'
    INSERT INTO items (id, name, rarity, descr)
    VALUES (:id, :name, :rarity, :descr)
    ON DUPLICATE KEY 
    UPDATE name = :name, rarity = :rarity, descr = :descr
SQL);

foreach ((include 'Loot/Item/ItemList.php') as $item) {
    $stmt->execute([
        'id' => $item['id'],
        'name' => $item['name'],
        'rarity' => $item['rarity'],
        'descr' => $item['description'],
    ]);
}

unset($tempDB);
