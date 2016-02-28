<?php

namespace LukeNZ\Reddit\Exceptions;

class TokenStorageException extends \Exception
{
    protected $message = "When a TokenStorageMethod of 'File' is passed, please set the location the file should be stored at";
}