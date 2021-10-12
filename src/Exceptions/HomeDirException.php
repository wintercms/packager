<?php

namespace Winter\Packager\Exceptions;

class HomeDirException extends PackagerException
{
    protected $message = 'Unable to write to home directory.';
}
