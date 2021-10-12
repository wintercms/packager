<?php

namespace Winter\Packager\Exceptions;

class ComposerJsonException extends PackagerException
{
    protected $message = 'Your composer.json file is invalid.';
}
