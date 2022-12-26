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
    public function get(string $package, ?string $version = null): ?array
    {
        if (isset($version)) {
            $versionNormalized = $this->versionParser->normalize($version);

            return $this->packages[$package][$versionNormalized] ?? null;
        }

        return $this->packages[$package] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $package, string $version, array $packageData): void
    {
        if (!isset($this->packages[$package])) {
            $this->packages[$package] = [];
        }

        $versionNormalized = $this->versionParser->normalize($version);

        $this->packages[$package][$versionNormalized] = $packageData;
    }

    /**
     * {@inheritDoc}
     */
    public function forget(string $package, ?string $version = null): void
    {
        if (isset($version)) {
            $versionNormalized = $this->versionParser->normalize($version);

            unset($this->packages[$package][$versionNormalized]);
            return;
        }

        unset($this->packages[$package]);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $package, ?string $version = null): bool
    {
        if (isset($version)) {
            $versionNormalized = $this->versionParser->normalize($version);

            return isset($this->packages[$package][$versionNormalized]);
        }

        return isset($this->packages[$package]);
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): void
    {
        $this->packages = [];
    }
}
