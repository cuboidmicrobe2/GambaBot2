<?php

declare(strict_types=1);

namespace Tools\Discord\Text;

use Tools\Discord\Text\HeaderFormatInterface;
use Tools\Discord\Text\TimerFormatInterface;
use Tools\Discord\Text\MentionTypeInterface;

abstract class Format
{
    final public static function code(string $text): string
    {
        return '`'.$text.'`';
    }

    final public static function italic(string $text): string
    {
        return '*'.$text.'*';
    }

    final public static function bold(string $text): string
    {
        return '**'.$text.'**';
    }

    final public static function strikeThrough(string $text): string
    {
        return '~~'.$text.'~~';
    }
    
    final public static function spoiler(string $text): string
    {
        return '||'.$text.'||';
    }

    final public static function maskedLink(string $text, string $url): string
    {
        return '['.$text.']('.$url.')';
    }

    /**
     * Create a header with a \n at the end.
     * 
     * @method  big
     * @method  medium
     * @method  small
     * @method  subtext
     */
    final public static function header(): HeaderFormatInterface
    {
        return new class implements HeaderFormatInterface 
        {
            public function big(string $header): string
            {
                return '# '.$header."\n";
            }

            public function medium(string $header): string
            {
                return '## '.$header."\n";
            }

            public function small(string $header): string
            {
                return '### '.$header."\n";
            }

            public function subtext(string $subtext): string
            {
                return '-# '.$subtext."\n";
            }
        };
    }

    final public static function codeBlock(string $language, string $code): string
    {
        return <<<BLOCK
        ```{$language}
        {$code}
        ```
        BLOCK;
    }

    /**
     * Create a discord timer
     * 
     * @method shortTime
     * @method longTime
     * @method shortDate
     * @method longDate
     * @method shortDateTime
     * @method longDateTime
     * @method relative
     */
    final public static function timer(): TimerFormatInterface
    {                              
        return new class implements TimerFormatInterface 
        {
            public function shotTime(int $timeStamp): string
            {
                return '<t:'.$timeStamp.':t>';
            }

            public function longTime(int $timeStamp): string
            {
                return '<t:'.$timeStamp.':T>';
            }

            public function shortDate(int $timeStamp): string
            {
                return '<t:'.$timeStamp.':d>';
            }

            public function longDate(int $timeStamp): string
            {
                return '<t:'.$timeStamp.':D>';
            }

            public function shortDateTime(int $timeStamp): string
            {
                return '<t:'.$timeStamp.':f>';
            }

            public function LongDateTime(int $timeStamp): string
            {
                return '<t:'.$timeStamp.':F>';
            }

            public function relative(int $timeStamp): string
            {
                return '<t:'.$timeStamp.':R>';
            }

            public function __toString()
            {
                return self::class;
            }
        };
    }

    /**
     * @method user
     * @method channel
     * @method command
     */
    final public static function mention(): MentionTypeInterface
    {
        return new class implements MentionTypeInterface
        {
            public function user(string|int $userId): string
            {
                return '<@'.$userId.'>';
            }

            public function channel(string|int $channelId): string
            {
                return '<#'.$channelId.'>';
            }

            public function command(string $commandName, string|int $commandId): string
            {
                return '</'.$commandName.':'.$commandId.'>';
            }
        };
    }
}