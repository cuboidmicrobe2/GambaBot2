<?php

declare(strict_types=1);

namespace Tools\Discord\Text;

interface TimerFormatInterface
{
    /**
     * 9:01 AM | 09:01
     */
    public function shotTime(int $timeStamp): string;

    /**
     * 9:01:00 AM | 09:01:00
     */
    public function longTime(int $timeStamp): string;

    /**
     * 11/28/2018 | 28/11/2018
     */
    public function shortDate(int $timeStamp): string;

    /**
     * 	November 28, 2018 | 28 November 2018
     */
    public function longDate(int $timeStamp): string;

    /**
     * November 28, 2018 9:01 AM | 28 November 2018 09:01
     */
    public function shortDateTime(int $timeStamp): string;

    /**
     * Wednesday, November 28, 2018 9:01 AM | Wednesday, 28 November 2018 09:01
     */
    public function LongDateTime(int $timeStamp): string;

    /**
     * 3 years ago | 3 years ago
     */
    public function relative(int $timeStamp): string;
}
