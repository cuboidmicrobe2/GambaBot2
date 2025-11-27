<?php

declare(strict_types=1);

namespace Debug;

use DateTimeImmutable;
use DateTimeZone;
use ReflectionObject;
use ReflectionProperty;

trait Debug
{
    /**
     *  Convert bytes
     */
    public static function convert($size): string
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

        return @round($size / 1024 ** $i = floor(log($size, 1024)), 2).' '.$unit[$i];
    }

    public static function getMemoryUsage(): string
    {
        return self::convert(memory_get_usage(true));
    }

    /**
     * Return array of feedback options to all true or false
     *
     * @param  bool  $feedback  The state of the feedback array
     */
    public static function feedback(bool $feedback): array
    {
        return ['all' => $feedback];
    }

    /**
     * Set $timer to timestamp using hrtime()
     */
    public static function startTimer(&$timer): void
    {
        $timer = -hrtime(true);
    }

    /**
     * After using startTimer(), calculate time passed from hrtime() timestamp. format: 0.0000000s (hrtime_dif/1e+9)
     */
    public static function endTimer(&$timer): void
    {
        $timer += hrtime(true);
        $timer /= 1e+9;
    }

    /**
     * Returns all PUBLIC properties of an object
     */
    public static function getObjectVars(object $obj): array
    {
        $objectVars = [];
        $reflection = new ReflectionObject($obj);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionPropery) {
            $property = $reflectionPropery->name;

            if (isset($obj->$property)) {
                $objectVars[$property] = is_object($obj->$property) ? self::getObjectVars($obj->$property) : $obj->$property;
                // echo $obj::class . ': ' . $property . PHP_EOL;
            }
        }

        return $objectVars;
    }

    /**
     * turn array one dimensional
     */
    public function oneDim(array $array, array &$oneDimArray): array
    {
        foreach ($array as $value) {
            if (is_array($value)) {
                $this->oneDim($value, $oneDimArray);
            } else {
                $oneDimArray[] = $value;
            }
        }

        return $oneDimArray;
    }

    private static function createUpdateMessage(string $sender, string $message, int $type = MessageType::DEBUG): string
    {
        $time = new DateTimeImmutable(timezone: new DateTimeZone('Europe/Stockholm'));
        if (is_int($type)) {
            $typeString = MessageType::toString($type);
        }

        $sender = preg_replace('/[^\\\\]*\\\\/', '', self::class); // temp test

        if (($type & MessageType::WARNING) !== 0) {
            return new CMDOutput()->add('['.$time->format('Y-m-d\TH:i:s.uP').'] '.$sender.$typeString.': '.$message, CMD_FONT_COLOR::YELLOW);
        }

        return '['.$time->format('Y-m-d\TH:i:s.uP').'] '.$sender.$typeString.': '.$message;

    }

    private static function createAllowedMessage(string $sender, string $message, int $type, int $allowed): ?string
    {
        if (($allowed & $type) !== 0) {

            return self::createUpdateMessage($sender, $message, $type);
        }

        return null;
    }

    /**
     * ["all" => true|false] for all true or false
     */
    private static function parseFeedbackOptions(array $feedback, array $feedbackBlueprint): array
    {
        if (array_key_exists('all', $feedback)) {
            if ($feedback['all'] === true) {
                foreach (array_keys($feedbackBlueprint) as $key) {
                    $feedback[$key] = true;
                }

                return $feedback;
            }
            if ($feedback['all'] === false) {
                foreach (array_keys($feedbackBlueprint) as $key) {
                    $feedback[$key] = false;
                }

                return $feedback;
            }
        }
        foreach (array_keys($feedbackBlueprint) as $feedbackType) {
            if (array_key_exists($feedbackType, $feedback)) {
                $feedbackBlueprint[$feedbackType] = $feedback[$feedbackType];
            }
        }

        return $feedbackBlueprint;
    }
}
