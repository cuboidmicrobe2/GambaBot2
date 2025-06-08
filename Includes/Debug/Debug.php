<?php

namespace Debug;

use DateTime;
use DateTimeZone;
use Debug\MessageType;
use Debug\CMDOutput;
use ReflectionObject;
use ReflectionProperty;

trait Debug {

    private static function createUpdateMessage(string $sender, string $message, int $type = MessageType::DEBUG) : string {
        $time = new DateTime(timezone: new DateTimeZone('Europe/Stockholm'));
        if(is_int($type)) $typeString = MessageType::toString($type);

        $sender = preg_replace('/[^\\\\]*\\\\/', '', self::class); // temp test

        if($type & MessageType::WARNING) return new CMDOutput()->add('['.$time->format('Y-m-d\TH:i:s.uP').'] '.$sender.$typeString.': '.$message, 33);
        
        return '['.$time->format('Y-m-d\TH:i:s.uP').'] '.$sender.$typeString.': '.$message;

    }

    private static function createAllowedMessage(string $sender, string $message, int $type, int $allowed) : ?string {
        if($allowed & $type) {

            return self::createUpdateMessage($sender, $message, $type);
        } 
        return null;
    }

    /**
     *  Convert bytes
     */
    public static function convert($size) {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }

    public static function getMemoryUsage() : string {
        return self::convert(memory_get_usage(true));
    }

    /**
     * turn array one dimensional
     */
    public function oneDim(array $array, array &$oneDimArray) : array {
        foreach($array as $value) {
            if(is_array($value)) $this->oneDim($value, $oneDimArray);
            else $oneDimArray[] = $value;
        }
        return $oneDimArray;
    }

    /**
     * ["all" => true|false] for all true or false
     */
    private static function parseFeedbackOptions(array $feedback, array $feedbackBlueprint) : array {
        if(array_key_exists("all", $feedback)) {
            if($feedback["all"] === true) {
                foreach($feedbackBlueprint as $key => $value) {
                    $feedback[$key] = true;
                }
                return $feedback;
            }
            elseif($feedback["all"] === false) {
                foreach($feedbackBlueprint as $key => $value) {
                    $feedback[$key] = false;
                }
                return $feedback;
            }
        }
        foreach($feedbackBlueprint as $feedbackType => $bool) {
            if(array_key_exists($feedbackType, $feedback)) $feedbackBlueprint[$feedbackType] = $feedback[$feedbackType];
        }
        return $feedbackBlueprint;
    }

    /**
     * Return array of feedback options to all true or false
     * 
     * @param bool  $feedback   The state of the feedback array
     */
    public static function feedback(bool $feedback) : array {
        return ["all" => $feedback];
    }

    /**
     * Set $timer to timestamp using hrtime()
     */
    public static function startTimer(&$timer) : void {
        $timer = 0.0000000;
        $timer =- hrtime(true);
    }
    
    /**
     * After using startTimer(), calculate time passed from hrtime() timestamp. format: 0.0000000s (hrtime_dif/1e+9)
     */
    public static function endTimer(&$timer) : void {
        $timer += hrtime(true);
        $timer = $timer/1e+9;
    }

    /**
     * Returns all PUBLIC properties of an object
     */
    public static function getObjectVars(object $obj) : array {
        $objectVars = [];
        $reflection = new ReflectionObject($obj);


        foreach($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionPropery) {
            $property = $reflectionPropery->name;

            if(isset($obj->$property)) {
                if(is_object($obj->$property)) {
                    $objectVars[$property] = self::getObjectVars($obj->$property);
                } 
                else $objectVars[$property] = $obj->$property;

                //echo $obj::class . ': ' . $property . PHP_EOL;
            } 
        }
        return $objectVars;
    }
}