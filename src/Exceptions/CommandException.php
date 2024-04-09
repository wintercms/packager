<?php

namespace Winter\Packager\Exceptions;

/**
 * Command exception.
 *
 * Handles an exception thrown from a Packager command.
 *
 * @author Ben Thomson
 * @since 0.2.0
 */
class CommandException extends PackagerException
{
    protected $message = 'Invalid command.';
}
