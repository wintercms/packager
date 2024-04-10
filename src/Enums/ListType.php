<?php

namespace Winter\Packager\Enums;

/**
 * List type enum.
 *
 * Defines the type of list we are querying with the `list` command.
 *
 * @author Ben Thomson
 * @since 0.3.0
 */
enum ListType: string
{
    /**
     * List all packages.
     */
    case ALL = 'all';
    /**
     * List packages from a certain namespace (vendor).
     */
    case NAMESPACE = 'name';
    /**
     * List packages of a certain type.
     */
    case TYPE = 'vendor';
}
