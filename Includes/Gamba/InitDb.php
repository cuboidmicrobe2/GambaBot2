<?php

new PDO('mysql:host='.$_ENV['DB_HOSTNAME'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'])->query(<<<SQL
    CREATE DATABASE IF NOT EXISTS gamba;
    USE gamba;

    CREATE TABLE IF NOT EXISTS items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(64) NOT NULL,
        rarity TINYINT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS inventory (
        uid INT PRIMARY KEY NOT NULL,
        //somehow save inventory//
    );

SQL);