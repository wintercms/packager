<?php

namespace BennoThommo\Packager\Commands;

use BennoThommo\Packager\Composer;

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
