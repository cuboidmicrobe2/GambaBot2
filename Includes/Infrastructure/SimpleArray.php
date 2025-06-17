<?php

namespace Infrastructure;

use ArrayAccess;
use Countable;
use Exception;
use Iterator;
use JsonSerializable;
use OutOfRangeException;
use ReturnTypeWillChange;
use SplFixedArray;

/**
 * A fixed array contaning a single type
 */
class SimpleArray implements ArrayAccess, Countable, Iterator, JsonSerializable {
    
    public readonly string $type;
    public readonly int $size;
    private int $position = 0;

    protected SplFixedArray $_data;
    

    public function __construct(string $dataType, int $size) {
        $this->_data = new SplFixedArray($size);
        $this->size = $size;
        $this->type = match($dataType) {
            'int' => 'integer',
            'bool' => 'boolean',
            'float' => 'double',
            default => $dataType
        };
    }

    /**
     * Insert data into next empty offsets
     */
    public function insert(mixed ...$values) : void {
        $inserted = 0;
        $size = count($values);
        
        $i = 0;

        do {
            if($this->_data[$i] === null) {
                

                $this[$i] = $values[$inserted];
                $inserted++;
                if($inserted == $size) return;
            }
            $i++;
        }
        while($i < $this->size);


        // for($i = 0; $i < $this->size; $i++) {

        // }
        throw new OutOfRangeException('Too many values to insert into ' . $this);  
    }

    /**
     * Get class name and storage type as string
     */
    public function __toString() : string {
        return self::class.'<'.$this->type.', '.$this->size.'>';
    }

    public function __debugInfo() {
        return $this->_data->toArray();
    }

    /**
     * Static constructor
     */
    public static function set(string $dataType, int $size) : self {
        return new static($dataType, $size);
    }

// ------------------ArrayAccess------------------

    public function offsetExists(mixed $offset): bool {
        return array_key_exists($offset, $this->_data->toArray());
    }

    public function offsetGet(mixed $offset): mixed {
        return $this->_data[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        $valueType = gettype($value);
        if($valueType == 'object') $valueType = get_class($value);

        if($valueType != $this->type) throw new Exception('Cannot add property of type ' . $valueType . ' into ' . $this);
        $this->_data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void {
        unset($this->_data[$offset]);
    }

// ------------------Countable------------------

    public function count(): int {
        return $this->size;
    }

// ------------------Iterator------------------

    public function rewind(): void {
        $this->position = 0;
    }

    #[ReturnTypeWillChange]
    public function current(): mixed {
        return $this->_data[$this->position];
    }

    public function next(): void {
        ++$this->position;
    }

    public function valid(): bool {
        return isset($this->_data[$this->position]);
    }

    #[ReturnTypeWillChange]
    public function key(): mixed {
        return $this->position;
    }

// ------------------JsonSerializable------------------

    public function jsonSerialize(): SplFixedArray
    {
        return $this->_data;
    }
}