<?php

namespace Winter\Packager\Storage;

use Composer\Semver\VersionParser;

/**
 * Memory storage.
 *
 * Stores package metadata in memory using an array. This is the default storage engine.
 *
 * This data survives for the length of the request lifecycle.
 *
 * @author Ben Thomson
 * @since 0.3.0
 */
class Memory implements Storage
{
    /**
     * @var array<string, array<string, array<string, string>>> The package metadata.
     */
    protected $packages = [];

    /**
     * @var VersionParser Version parser instance.
     */
    protected $versionParser;

    /**
     * Constructor.
     */
    public function __construct(?VersionParser $versionParser = null)
    {
        $this->versionParser = $versionParser ?? new VersionParser();
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $namespace, string $name, ?string $version = null): ?array
    {
        if (isset($version)) {
            $versionNormalized = $this->versionParser->normalize($version);

            return $this->packages[$this->packageName($namespace, $name)][$versionNormalized] ?? null;
        }

        return $this->packages[$this->packageName($namespace, $name)] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $namespace, string $name, string $version, array $packageData): void
    {
        if (!isset($this->packages[$this->packageName($namespace, $name)])) {
            $this->packages[$this->packageName($namespace, $name)] = [];
        }

        $versionNormalized = $this->versionParser->normalize($version);

        $this->packages[$this->packageName($namespace, $name)][$versionNormalized] = $packageData;
    }

    /**
     * {@inheritDoc}
     */
    public function forget(string $namespace, string $name, ?string $version = null): void
    {
        if (isset($version)) {
            $versionNormalized = $this->versionParser->normalize($version);

            unset($this->packages[$this->packageName($namespace, $name)][$versionNormalized]);
            return;
        }

        unset($this->packages[$this->packageName($namespace, $name)]);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $namespace, string $name, ?string $version = null): bool
    {
        if (isset($version)) {
            $versionNormalized = $this->versionParser->normalize($version);

            return isset($this->packages[$this->packageName($namespace, $name)][$versionNormalized]);
        }

        return isset($this->packages[$this->packageName($namespace, $name)]);
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): void
    {
        $this->packages = [];
    }

    protected function packageName(string $namespace, string $name): string
    {
        return $namespace . '/' . $name;
    }
}
