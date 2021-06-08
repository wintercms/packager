<?php

namespace BennoThommo\Packager\Commands;

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
    public function getCommandName(): string
    {
        return 'install';
    }
}
