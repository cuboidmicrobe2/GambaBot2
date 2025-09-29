<?php

namespace Infrastructure;

use Stringable;
use ArrayAccess;
use Countable;
use Exception;
use Generator;
use IteratorAggregate;
use JsonSerializable;
use OutOfRangeException;
use SplFixedArray;
use Traversable;

/**
 * @template TValue
 * @property int $size  Size of the object
 */
class SimpleArray implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Stringable
{
    public readonly string $type;

    /**
     * Max size of the array.
     */
    public readonly int $size;

    protected SplFixedArray $_data;

    public function __construct(string $dataType, int $size) {
        $this->_data = new SplFixedArray($size);
        $this->size = $size;
        $this->type = match(strtolower($dataType)) {
            'int' => 'integer',
            'bool' => 'boolean',
            'float' => 'double',
            'closure' => 'Closure',
            default => $dataType
        };
    }

    /**
     * Insert data into next empty offsets
     * 
     * @param TValue|TValue[]  $values
     * @throws OutOfRangeException  If inserting to many values
     */
    public function insert(mixed ...$values) : void {
        $inserted = 0;
        $size = count($values);
        
        $i = 0;

        do {
            if($this->_data[$i] === null) {
            
                $this[$i] = $values[$inserted];
                $inserted++;
                if ($inserted === $size) {
                    return;
                }
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
    final public function offsetExists(mixed $offset): bool {
        return array_key_exists($offset, $this->_data->toArray());
    }

    /**
     * @return TValue
     */
    final public function offsetGet(mixed $offset): mixed {
        return $this->_data[$offset];
    }

    /**
     * @param TValue    $value
     */
    final public function offsetSet(mixed $offset, mixed $value): void {
        $valueType = gettype($value);
        if ($valueType === 'object') {
            $valueType = $value::class;
        }

        if ($valueType !== $this->type) {
            throw new Exception('Cannot add property of type ' . $valueType . ' into ' . $this);
        }
        $this->_data[$offset] = $value;
    }

    final public function offsetUnset(mixed $offset): void {
        unset($this->_data[$offset]);
    }

    /**
     * Counts all non null values in the array.
     */
    final public function count(): int {
        $count = 0;
        foreach ($this->_data as $value) {
            if ($value !== null) {
                $count++;
            }
        }

        return $count;
    }

    final public function jsonSerialize(): SplFixedArray
    {
        return $this->_data;
    }

    final public function getIterator(): Traversable
    {
        return $this->_data->getIterator();
    }

    /**
     * Yeild all non null values.
     * @return Generator<int, TValue>
     */
    final public function yield(): Generator
    {
        foreach ($this->_data->getIterator() as $key => $value) {
            if ($value !== null) {
                yield $key => $value;
            }
        }  
    }

    /**
     * Filters elements of an array using a callback function.
     * @link https://www.php.net/manual/en/function.array-find.php
     * @return array<int, TValue>
     */
    final public function filter(callable $callback): array
    {
        $found = [];
        foreach ($this->_data->getIterator() as $value) {
            if ($value === null) {
                continue;
            }
            if ($callback($value)) {
                $found[] = $value;
            }
        }

        return $found;
    }

    /**
     * @param bool $ignoreNull  If true does not call callback on null values
     * @return array<int, TValue>
     */
    final public function map(callable $callback, bool $ignoreNull = true): array
    {
        $map = [];
        if ($ignoreNull) {
            foreach ($this->yield() as $value) {
                $map[] = $callback($value);
            }
        } else {
            foreach ($this->_data->getIterator() as $value) {
                $map[] = $callback($value);
            }    
        }

        return $map;
    }

    /**
     * Checks if at least one array element satisfies a callback function.
     */
    final public function any(callable $callback): bool
    {
        foreach ($this->_data->getIterator() as $value) {
            if ($value === null) {
                continue;
            }
            if ($callback($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if all array elements satisfy a callback function.
     */
    final public function all(callable $callback): bool
    {
        foreach ($this->_data->getIterator() as $value) {
            if ($value === null) {
                continue;
            }
            if (! $callback($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Pop the element off the end of array.
     * @return null|TValue
     */
    final public function pop(): mixed
    {
        $lastValue = null;
        for ($i = $this->size - 1; $i > 0; $i--) {
            if ($this[$i] !== null) {
                $lastValue = $this[$i];
                unset($this[$i]);
                return $lastValue;
            }
        }

        return null;
    }

    /**
     * Shift an element off the beginning of the array.
     * @return null|TValue
     */
    final public function shift(): mixed
    {
        $fistValue = null;
        for ($i = 0; $i < $this->size; $i++) {
            $fistValue ??= $this[$i];

            if ($i === 0 || $this[$i] === null) {
                continue;
            }

            $this[$i - 1] = $this[$i];
            unset($this[$i]);
        }
        return $fistValue;
    }

    /**
     * Prepend elements to the beginning of an array
     * 
     * @param TValue $value value to be added
     */
    final public function unshift(mixed $value): void
    {
        
    }

    /**
     * @param TValue    $value
     */
    final public function push(mixed $value): void
    {
        for ($i = $this->size - 1; $i > 0; $i--) {
            if ($this[$i] !== null) {
                $this[$i + 1] = $value;
            }
        }
    }
    /**
     * Sets all values to null.
     */
    final public function unset(): void
    {
        for ($i = 0; $i < $this->size; $i++) {
            $this[$i] = null;
        }
    }
    
    /**
     * @return TValue   random value
     */
    final public function rand(bool $includeNull = false): mixed
    {
        if ($includeNull) {
            $key = mt_rand(0, $this->size - 1);

            return $this->_data[$key];
        }

        $value = null;
        do {
            $value = $this->_data[mt_rand(0, $this->size - 1)];
        } while ($value === null);

        return $value;
    }
}