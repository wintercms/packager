<?php

namespace Winter\Packager\Enums;

/**
 * Show modes.
 *
 * This defines all the available modes for the `composer show` command that Packager supports.
 *
 * @author Ben Thomson
 * @since 0.1.0
 */
enum ShowMode: string
{
    /**
     * Show only installed packages.
     */
    case INSTALLED = 'installed';
    /**
     * Show packages contained in lock file.
     */
    case LOCKED = 'locked';
    /**
     * Show only platform dependencies - ie. PHP version and extensions.
     */
    case PLATFORM = 'platform';
    /**
     * Show available packages, including all uninstalled packages listed in Composer.
     */
    case AVAILABLE = 'available';
    /**
     * Show the current package.
     */
    case SELF = 'self';
    /**
     * Show the paths to all installed packages.
     */
    case PATH = 'path';
    /**
     * Display package information in a dependency tree format.
     */
    case TREE = 'tree';
    /**
     * Show packages that are outdated and have available updates.
     */
    case OUTDATED = 'outdated';
    /**
     * Show only installed packages directly required by the current package (not dependencies of dependencies).
     */
    case DIRECT = 'direct';

    /**
     * Determines if this mode returns a collection of packages and should be collated.
     */
    public function isCollectible(): bool
    {
        return in_array($this, [
            static::INSTALLED,
            static::LOCKED,
            static::PLATFORM,
            static::AVAILABLE,
            static::OUTDATED,
            static::DIRECT,
        ]);
    }

    /**
     * Determines if this mode queries only local packages, and can support querying of the lock file.
     */
    public function isLocal(): bool
    {
        return in_array($this, [
            static::INSTALLED,
            static::LOCKED,
            static::OUTDATED,
            static::DIRECT,
        ]);
    }

    /**
     * Gets the array key we expect from the JSON output from the Composer `show` command.
     */
    public function getComposerArrayKeyName(): string
    {
        return match ($this) {
            static::LOCKED => 'locked',
            static::PLATFORM => 'platform',
            static::AVAILABLE => 'available',
            default => 'installed',
        };
    }
}
