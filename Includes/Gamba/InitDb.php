<?php

new PDO('mysql:host='.$_ENV['DB_HOSTNAME'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'])->query(<<<SQL
    CREATE DATABASE IF NOT EXISTS gamba;
    CREATE DATABASE IF NOT EXISTS gamba_inventories;
    USE gamba;

    CREATE TABLE IF NOT EXISTS items (
        id TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(64) NOT NULL,
        rarity TINYINT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS coin_inventory (
        uid BIGINT UNSIGNED PRIMARY KEY NOT NULL,
        coins BIGINT UNSIGNED
    );
SQL);