<?php

declare(strict_types=1);

namespace Infrastructure;

use ArrayAccess;
use Generator;
use WeakMap;
use WeakReference;

/**
 * Object for caching other objects.
 *
 * @template TKey of string|int
 * @template TValue of object
 */
final class ObjectCach implements ArrayAccess
{
    /**
     * Send to **Generator** to remove current object.
     */
    public const int REMOVE_OBJECT = 1;

    /**
     * Total amount of WeakReferences in the object.
     */
    public int $size {
        get {
            return count($this->_internalCach);
        }
    }

    /**
     * An array that stores a Weakreferences.
     *
     * @var array<TKey, WeakReference<TValue>>
     */
    private array $_internalCach = [];

    /**
     * @var WeakMap<TValue, array>
     */
    private WeakMap $_internalData;

    public function __construct()
    {
        $this->_internalData = new WeakMap;
    }

    /**
     * @param  TKey  $name  Property name.
     * @return null|TValue
     */
    public function __get(string|int $name): ?object
    {
        return $this->get($name);
    }

    /**
     * @param  TKey  $name  Property name.
     * @param  TValue  $value  Object to cach.
     */
    public function __set(string|int $name, object $value)
    {
        $this->set($name, $value);
    }

    public function __debugInfo()
    {
        return $this->_internalCach;
    }

    /**
     * Get object from cach if it exists.
     *
     * @param  TKey  $ident
     * @return null|TValue
     */
    public function get(string|int $ident): ?object
    {
        $ref = ($this->_internalCach[$ident] ?? null)?->get();

        if ($ref === null && $this->exists($ident)) {
            unset($this->_internalCach[$ident]);
        }

        return $ref;
    }

    /**
     * Get data array associated with an identifier or object.
     *
     * @param  TValue|TKey  $value  Identifier or object.
     */
    public function getData(object|string|int $value): ?array
    {
        if (! is_object($value)) {
            $value = $this->get($value);
        }

        return $this->_internalData[$value] ?? null;
    }

    /**
     * Add object to cach.
     *
     * @param  TKey  $ident  Identifier for an object.
     * @param  TValue  $object  Object to cach.
     */
    public function set(string|int $ident, object $object, ?array $data = null): void
    {
        $this->_internalCach[$ident] = WeakReference::create($object);

        if ($data !== null) {
            $this->_internalData[$object] = $data;
        }
    }

    /**
     * @param  TKey  $ident
     * @return null|TValue Removed object or null
     */
    public function remove(string|int $ident): ?object
    {
        if (! isset($this->_internalCach[$ident])) {
            return null;
        }

        $object = $this->get($ident);
        unset($this->_internalCach[$ident]);

        return $object;
    }

    /**
     * Clear all removed objects.
     */
    public function clean(): void
    {
        foreach ($this->_internalCach as $ident => $WeakReference) {
            if ($WeakReference->get() === null) {
                unset($this->_internalCach[$ident]);
            }
        }
    }

    /**
     * Count all references that are not null.
     */
    public function countValid(): int
    {
        $count = 0;

        foreach ($this->_internalCach as $weakRef) {
            if ($weakRef->get() !== null) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check if there is a **TValue** linked to the **TKey** regardless of cached object status.
     * 
     * @param TKey $ident
     */
    public function exists(string $ident): bool
    {
        return isset($this->_internalCach[$ident]);
    }

    /**
     * Check if a cached object is still valid.
     * 
     * @param TKey $ident
     */
    public function valid(string $ident): bool
    {
        return ($this->_internalCach[$ident] ?? null)?->get() !== null;
    }

    /**
     * @return Generator<TKey, TValue>
     */
    public function createGenerator(): Generator
    {
        foreach ($this->_internalCach as $ident => $weakRef) {
            $return = yield $ident => $weakRef->get();

            switch ($return) {
                case self::REMOVE_OBJECT:
                    $this->remove($ident);
                    break;
                default:
                    break;
            }
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->exists($offset);
    }

    public function offsetGet(mixed $offset): ?object
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->_internalCach[$offset]);
    }
}
