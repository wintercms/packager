<?php

namespace BennoThommo\Packager\Exceptions;

use Exception;

class PackagerException extends Exception
{
    protected $message = 'An unknown Composer exception occurred.';
}
