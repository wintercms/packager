<?php

namespace Winter\Packager\Package;

/**
 * Package collection.
 *
 * Collections contain one or more packages from a given result set, and can be used to filter and traverse the results.
 *
 * @author Ben Thomson <git@alfreido.com>
 * @since 0.3.0
 * @implements \ArrayAccess<int|string, Package>
 * @implements \Iterator<int, Package>
 */
class Collection implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var array<int, Package> The packages contained in the collection.
     */
    protected array $items = [];

    /**
     * Present position in the collection.
     */
    protected int $position = 0;

    /**
     * Constructor.
     *
     * @param Package[]|Package $items
     */
    final public function __construct(...$items)
    {
        foreach ($items as $item) {
            if ($item instanceof Package) {
                $this->items[] = $item;
            } elseif (is_array($item)) {
                foreach ($item as $subItem) {
                    if ($subItem instanceof Package) {
                        $this->items[] = $subItem;
                    }
                }
            }
        }
    }

    /**
     * Adds a package to this collection.
     */
    protected function add(Package $package): void
    {
        $this->items[] = $package;

        uasort($this->items, function (Package $a, Package $b) {
            return $a->getPackageName() <=> $b->getPackageName();
        });
    }

    /**
     * Gets the count of packages in this collection.
     */
    public function count(): int
    {
        return count($this->items);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): mixed
    {
        return $this->items[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }

    /**
     * Gets a package at a given index.
     *
     * This does not reset the internal pointer of the collection.
     *
     * If no package is found at the given index, `null` is returned.
     */
    public function get(int $index): ?Package
    {
        return $this->items[$index] ?? null;
    }

    /**
     * Finds a given package in the collection.
     */
    public function find(string $namespace, string $name = '', ?string $version = null): ?Package
    {
        if (empty($name) && strpos($namespace, '/') !== false) {
            [$namespace, $name] = explode('/', $namespace, 2);
        }

        foreach ($this->items as $item) {
            if ($item->getNamespace() === $namespace && $item->getName() === $name) {
                if (is_null($version) || ($item instanceof VersionedPackage && $item->getVersion() === $version)) {
                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * Checks if a given offset exists.
     *
     * You may either provide an integer key to retrieve by index, or a string key in the format `namespace/name` to
     * find a particular package.
     */
    public function offsetExists(mixed $offset): bool
    {
        if (is_int($offset)) {
            return isset($this->items[$offset]);
        }

        if (is_string($offset)) {
            [$namespace, $name] = explode('/', $offset, 2);
            return !is_null($this->find($namespace, $name));
        }
    }

    /**
     * Gets a package at a given offset.
     *
     * You may either provide an integer key to retrieve by index, or a string key in the format `namespace/name` to
     * find a particular package.
     */
    public function offsetGet(mixed $offset): ?Package
    {
        if (is_int($offset)) {
            return $this->get($offset);
        }

        if (is_string($offset)) {
            [$namespace, $name] = explode('/', $offset, 2);
            return $this->find($namespace, $name);
        }
    }

    /**
     * Sets a package at a given offset.
     *
     * This method is not supported.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \RuntimeException('You cannot set values in a package collection.');
    }

    /**
     * Unsets a package at a given offset.
     *
     * This method is not supported.
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new \RuntimeException('You cannot unset values in a package collection.');
    }

    /**
     * Retrieve the collection as an array.
     *
     * @return array<int, \Winter\Packager\Package\Package>
     */
    public function toArray()
    {
        return $this->items;
    }

    /**
     * Converts all packages within the collection to detailed packages.
     */
    public function toDetailed(): static
    {
        foreach ($this->items as &$package) {
            if ($package instanceof DetailedPackage) {
                continue;
            }

            $package = $package->toDetailed();
        }

        return $this;
    }

    /**
     * Filters the collection by a given type, returning a new collection.
     */
    public function type(string $type): static
    {
        $filtered = array_filter($this->items, function (Package $package) use ($type) {
            return $package->getType() === $type;
        });

        return new static($filtered);
    }
}
