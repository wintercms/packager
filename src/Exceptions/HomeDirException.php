<?php namespace BennoThommo\Packager\Exceptions;

class HomeDirException extends PackagerException
{
    protected $message = 'Unable to write to home directory.';
}
