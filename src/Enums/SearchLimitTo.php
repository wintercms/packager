<?php

namespace Winter\Packager\Enums;

/**
 * Search limiting mode.
 *
 * This defines what information should be limited down to when searching packages with the `composer search` command.
 *
 * @author Ben Thomson
 * @since 0.1.0
 */
enum SearchLimitTo: string
{
    /**
     * Display all details of a package when searching.
     */
    case ALL = 'all';
    /**
     * Only display the name of the package when searching.
     */
    case NAME_ONLY = 'name';
    /**
     * Only display the namespace (vendor) of the packages that match your search.
     */
    case NAMESPACE_ONLY = 'vendor';
}
