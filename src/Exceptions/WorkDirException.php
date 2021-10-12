<?php

namespace Winter\Packager\Exceptions;

class WorkDirException extends PackagerException
{
    protected $message = 'Unable to write to working directory.';
}
