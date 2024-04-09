<?php

namespace Winter\Packager\Commands;

/**
 * Install command.
 *
 * Runs "composer install" within PHP.
 *
 * @author Ben Thomson
 * @since 0.1.0
 */
class Install extends Update
{
    /**
     * @inheritDoc
     */
    protected function getCommandName(): string
    {
        return 'install';
    }
}
