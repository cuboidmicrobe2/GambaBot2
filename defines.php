<?php

declare(strict_types=1);

/**
 * Time zone used in DateTimeZone
 */
define('TIME_ZONE', 'Europe/Stockholm');

/**
 * Hex color value (light pink)
 */
define('EMBED_COLOR_PINK', 'F9C6CE');

/**
 * Hex color value (pastel red)
 */
define('EMBED_COLOR_RED', 'FF6961');

/**
 * Hex color value (pastel green)
 */
define('EMBED_COLOR_GREEN', '77DD77');

/**
 * The price of one /wish
 */
define('WISH_PRICE', 1000);

/**
 * Pity needed for guaranteed gold item
 */
define('GOLD_PITY_CAP', 80);

/**
 * Pity needed for guaranteed purple item
 */
define('PURPLE_PITY_CAP', 10);

/**
 * Gold chance will increase with this value
 */
define('GOLD_SOFT_PITY', (int) floor(GOLD_PITY_CAP * 0.92));

/**
 * Max value in mt_rand(1, **PROB_MAX**)
 */
define('PROB_MAX', 10000);

/**
 * Value to be added every roll after **GOLD_SOFT_PITY**
 */
define('SOFT_PITY_ADDER', 0.1 * PROB_MAX);

/**
 * Value that will adjust the rng ranges
 *
 * @param  int  $value  goldPity after **GOLD_SOFT_PITY**
 * @return int Value that will adjust the rng ranges
 */
define('GOLD_RANGE_ADJUSTER', static fn (float $value): int => (int) (($value - GOLD_SOFT_PITY) * SOFT_PITY_ADDER));

/**
 * Min blue roll
 */
define('BLUE_MIN', 1);

/**
 * Max blue roll
 */
define('BLUE_MAX', 9200);

/**
 * Min purple roll
 */
define('PURPLE_MIN', 9201);

/**
 * Max purple roll
 */
define('PURPLE_MAX', 9800);

/**
 * Min gold roll
 */
define('GOLD_MIN', 9801);

/**
 * Max gold roll (equal to **PROB_MAX**)
 */
define('GOLD_MAX', PROB_MAX);

// ----------------------------------- Discord defines: -----------------------------------

/**
 * MessageBuilder flag
 */
define('SILENT_MESSAGE', 4096);

/**
 * Discord will parse this in to a commandlink to /daily
 */
define('COMMAND_LINK_DAILY', '</daily:1386666528983875695>');

// ----------------------------------- Bot misc: -----------------------------------

// define('PRESENCE_STRINGS', [
//     'Gambling🥰😍',
//     'Lost it all🥺',
//     'Big wins💸💸💸',
//     '/daily = 💰💰💰',
//     '🎰🎰🎰'
// ]);
