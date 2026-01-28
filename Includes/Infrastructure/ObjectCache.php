<?php

declare(strict_types=1);

namespace Infrastructure;

use ArrayAccess;
use Generator;
use InvalidArgumentException;
use Override;
use WeakMap;
use WeakReference;

/**
 * Object for caching other objects.
 *
 * @template TKey of string|int
 * @template TValue of object
 * @implements ArrayAccess<TKey, TValue>
 */
final class ObjectCache implements ArrayAccess
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
            return count($this->_internalCache);
        }
    }

    /**
     * An array that stores a **WeakReference**.
     *
     * @var array<TKey, WeakReference<TValue>>
     */
    private array $_internalCache = [];

    /**
     * @var WeakMap<TValue, array<int|string, mixed>>
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
     * @param  TValue  $value  Object to cache.
     */
    public function __set(string|int $name, object $value)
    {
        $this->set($name, $value);
    }

    /**
     * This method is called by {@see \var_dump() var_dump()} when dumping an object to get the properties that should be shown.
     *
     * @return array<string|int, WeakReference<TValue>>
     */
    public function __debugInfo(): array
    {
        return $this->_internalCache;
    }

    /**
     * Get object from cache if it exists.
     *
     * @param  TKey  $ident
     * @return null|TValue
     */
    public function get(string|int $ident): ?object
    {
        $ref = ($this->_internalCache[$ident] ?? null)?->get();

        if ($ref === null && $this->exists($ident)) {
            unset($this->_internalCache[$ident]);
        }

        return $ref;
    }

    /**
     * Gets the full **WeakReference** form an identifier.
     *
     * @param TKey $ident
     * @return null|WeakReference<TValue> The **WeakReference** or null if it does not exits.
     */
    public function getWeak(string|int $ident): ?WeakReference
    {
        return $this->_internalCache[$ident] ?? null;
    }

    /**
     * Get data array associated with an identifier or object.
     *
     * @param  TValue|TKey  $value  Identifier or object.
     * @return array<string|int, mixed>
     */
    public function getData(object|string|int $value): array
    {
        if (! is_object($value)) {
            $value = $this->get($value);
        }

        return $this->_internalData[$value] ?? [];
    }

    /**
     * Add object to cache.
     *
     * @param  TKey  $ident  Identifier for an object.
     * @param  TValue  $object  Object to cache.
     * @param array<int|string, mixed> $data
     */
    public function set(string|int $ident, object $object, ?array $data = null): void
    {
        $this->_internalCache[$ident] = WeakReference::create($object);

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
        if (! isset($this->_internalCache[$ident])) {
            return null;
        }

        $object = $this->get($ident);
        unset($this->_internalCache[$ident]);

        return $object;
    }

    /**
     * Clear all removed objects.
     */
    public function clean(): void
    {
        foreach ($this->_internalCache as $ident => $WeakReference) {
            if ($WeakReference->get() === null) {
                unset($this->_internalCache[$ident]);
            }
        }
    }

    /**
     * Count all references that are not null.
     */
    public function countValid(): int
    {
        $count = 0;

        foreach ($this->_internalCache as $weakRef) {
            if ($weakRef->get() !== null) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check if there is a **TValue** linked to the **TKey** regardless of cached object status.
     *
     * @param  TKey  $ident
     */
    public function exists(string|int $ident): bool
    {
        return isset($this->_internalCache[$ident]);
    }

    /**
     * Check if a cached object is still valid.
     *
     * @param  TKey  $ident
     */
    public function valid(string|int $ident): bool
    {
        return ($this->_internalCache[$ident] ?? null)?->get() !== null;
    }

    /**
     * @return Generator<TKey, null|TValue>
     */
    public function createGenerator(): Generator
    {
        foreach ($this->_internalCache as $ident => $weakRef) {
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
    
    /**
     * Whether a offset exists
     *
     * @param TKey $offset
     * @return boolean
     */
    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        return $this->exists($offset);
    }

    /**
     * Offset to retrieve
     *
     * @param TKey $offset
     * @return TValue|null
     */
    #[Override]
    public function offsetGet(mixed $offset): ?object
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @param TKey|null $offset
     * @param TValue $value
     * @return void
     * @throws InvalidArgumentException if $offset is null.
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            throw new InvalidArgumentException('$offset cannot be null');
        }
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @param TKey $offset
     * @return void
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        unset($this->_internalCache[$offset]);
    }
}
