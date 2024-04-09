<?php

namespace Winter\Packager\Exceptions;

/**
 * Packagist exception.
 *
 * Handles an exception thrown when communicating with the Packagist API.
 *
 * @author Ben Thomson
 * @since 0.3.0
 */
class PackagistException extends PackagerException
{
    protected $message = 'Unable to connect to or retrieve a valid response from Packagist.';
}
