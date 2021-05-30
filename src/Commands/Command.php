<?php namespace BennoThommo\Packager\Commands;

use Symfony\Component\Console\Input\ArrayInput;

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
     * @return boolean If the command is successfully run.
     */
    public function execute(): bool;

    /**
     * Provides the command name for Composer.
     *
     * @return string
     */
    public function getCommandName(): string;

    /**
     * Provides if the given command requires the working directory to be available.
     *
     * @return bool True if it does, false if it does not.
     */
    public function requiresWorkDir(): bool;

    /**
     * Provides the arguments for the wrapped Composer command.
     *
     * @return array An array of arguments to provide the Composer application.
     */
    public function arguments(): array;
}
