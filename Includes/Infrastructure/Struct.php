<?php

declare(strict_types=1);

namespace Infrastructure;

use Deprecated;
use Exception;
use Generator;
use Infrastructure\Exceptions\CannotMutateStructException;
use SensitiveParameter;
use SplFixedArray;

/**
 * @author edwin030426@gmail.com
 */
final class Struct
{ // wip

    private SplFixedArray $properties;

    /**
     * Create readonly class from array, e.g:
     *
     * $foo = new Struct([
     *     'value1' => 1,
     *     'value2' => 'Hello World'
     * ]);
     *
     * @param  array  $vars  key is property name, and value is property value
     */
    public function __construct(array $vars)
    {
        $this->properties = new SplFixedArray(count($vars) * 2);

        $i = 0;
        foreach ($vars as $varName => $val) {
            if (! is_string($varName)) {
                throw new Exception('variable name must be of type string');
            }

            $this->properties[$i] = $varName;
            $i++;
            $this->properties[$i] = $val;
            $i++;
        }
    }

    // public function __invoke() : mixed {
    //     return (array_key_exists('__invoke', $this->properties) AND is_callable($this->properties['__invoke'])) ? call_user_func($this->properties['__invoke']) : null;
    // }

    public function __get(string $name): mixed
    {
        // if(!array_key_exists($name, $this->properties)) throw new Exception('Cannot access value of unset property: "' . $name . '"');

        // if(is_callable($this->properties[$name])) throw new Exception('Cannot "get" on property type callable: ' . $name . '()');

        // return $this->properties[$name];

        foreach ($this->properties() as $property) {
            if ($property['name'] === $name) {
                return $property['value'];
            }
        }

        throw new Exception('Cannot access value of unset property: "'.$name.'"');
    }

    public function __call(string $name, mixed $args): mixed
    {

        // if(!array_key_exists($name, $this->properties)) throw new Exception('Cannot access value of unset property: "' . $name . '"');

        // if(!is_callable($this->properties[$name])) throw new Exception('Cannot call undefined property');

        // return call_user_func($this->properties[$name], $args);
        foreach ($this->properties() as $property) {
            if ($property['name'] === $name) {
                if (! is_callable($property['value'])) {
                    throw new Exception('Cannot call none callable property');
                }

                return call_user_func($property['value'], $args);
            }
        }
        throw new Exception('Cannot call unset property: "'.$name.'"');
    }

    public function __set(string $name, #[SensitiveParameter] mixed $value): void
    {

        throw new CannotMutateStructException('Cannot change value of property in struct');
    }

    // public function __toString() : string {
    //     return (array_key_exists('__toString', $this->properties)) ? $this->properties['__toString'] : self::class;
    // }

    public function __debugInfo(): array
    {
        return $this->properties->toArray();
    }

    public function toArray(): array
    {
        return $this->properties->toArray();
    }

    #[Deprecated('Use normal get')]
    public function getPropertie(string $name): mixed
    {
        return $this->properties[$name] ?? null;
    }

    private function properties(): Generator
    {
        for ($i = 0; $i < $this->properties->getSize(); $i += 2) {
            yield [
                'name' => $this->properties[$i],
                'value' => $this->properties[$i + 1],
            ];
        }
    }

    // function fromStdClass()
}

/**
 * Notes!
 *
 * * Function arguments must be stored in an array
 *
 * * No args on invoke
 */
