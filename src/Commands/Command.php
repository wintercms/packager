<?php

namespace Winter\Packager\Commands;

/**
 * Command.
 *
 * A command is an individual wrapper around a Composer command.
 *
 * @author Ben Thomson
 * @since 0.1.0
 */
interface Command
{
    /**
     * Executes the command with the given options.
     *
     * @return mixed The output of the command.
     */
    public function execute();
}
