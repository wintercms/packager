<?php

namespace Winter\Packager\Storage;

/**
 * Storage.
 *
 * A storage object contains metadata for packages, and can either be ephemeral or persistent. This
 * is used to avoid repeated retrievals of package metadata from Composer.
 *
 * @author Ben Thomson
 * @since 0.3.0
 */
interface Storage
{
    /**
     * Retrieves the package metadata for the given package name.
     *
     * If the version is null, it retrieves all versions of the package metadata stored.
     *
     * @return array<string|int, mixed>|null
     */
    public function get(string $package, ?string $version = null): ?array;

    /**
     * Sets the package metadata for the given package name and version.
     *
     * @param array<string, mixed> $packageData
     */
    public function set(string $packageName, string $version, array $packageData): void;

    /**
     * Forgets the package metadata for the given package name.
     *
     * You can either specify a version to remove the metadata for a single version of a packaage,
     * or leave it null to remove the entire package.
     *
     * This should be a no-op if the package metadata does not exist.
     */
    public function forget(string $package, ?string $version = null): void;

    /**
     * Determines if the package metadata exists in storage, optionally with the specified version.
     */
    public function has(string $package, ?string $version = null): bool;

    /**
     * Clears all package metadata.
     */
    public function clear(): void;
}
