<?php

$tempDB = PDO::connect('mysql:host='.$_ENV['DB_HOSTNAME'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
$tempDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$tempDB->query(<<<SQL
    CREATE DATABASE IF NOT EXISTS gamba;
    CREATE DATABASE IF NOT EXISTS gamba_inventories;
    USE gamba;

    CREATE TABLE IF NOT EXISTS items (
        id TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(64) NOT NULL,
        rarity TINYINT NOT NULL
    );

    USE gamba_inventories;

    CREATE TABLE IF NOT EXISTS user_stats (
        uid BIGINT UNSIGNED PRIMARY KEY NOT NULL,
        coins BIGINT UNSIGNED DEFAULT 0,
        purple_pity SMALLINT UNSIGNED DEFAULT 0,
        gold_pity SMALLINT UNSIGNED DEFAULT 0
    );
SQL);
unset($tempDB);