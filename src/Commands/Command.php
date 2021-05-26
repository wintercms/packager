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
     * Provides the arguments for the wrapped Composer command.
     *
     * @return ArrayInput An array of arguments to provide the Composer application.
     */
    protected function arguments(): ArrayInput;
}