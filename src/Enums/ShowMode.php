<?php

namespace Winter\Packager\Enums;

/**
 * Show modes.
 *
 * This defines all the available modes for the `composer show` command that Packager supports.
 */
enum ShowMode: string
{
    case INSTALLED = 'installed';
    case LOCKED = 'locked';
    case PLATFORM = 'platform';
    case AVAILABLE = 'available';
    case SELF = 'self';
    case PATH = 'path';
    case TREE = 'tree';
    case OUTDATED = 'outdated';
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
            static::SELF,
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
