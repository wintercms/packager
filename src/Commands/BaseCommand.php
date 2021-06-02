<?php

namespace BennoThommo\Packager\Commands;

use BennoThommo\Packager\Composer;

/**
 * Base Command class.
 *
 * Commands should extend this one to meet the specification for commands.
 *
 * @author Ben Thomson
 * @since 0.1.0
 */
abstract class BaseCommand implements Command
{
    /**
     * @var Composer Composer instance.
     */
    protected $composer;

    /**
     * Constructor.
     *
     * Defines the Composer instance that will run the command.
     *
     * @param Composer $composer
     */
    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
    }

    /**
     * Returns the instance of Composer that is running the command.
     *
     * @return Composer
     */
    public function getComposer(): Composer
    {
        return $this->composer;
    }

    /**
     * @inheritDoc
     */
    public function getCommandName(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function requiresWorkDir(): bool
    {
        return false;
    }
}
