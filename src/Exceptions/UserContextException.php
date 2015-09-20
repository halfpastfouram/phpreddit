<?php
namespace LukeNZ\Reddit\Exceptions;

class UserContextException extends \Exception {
    protected $message = "Please set a user using the 'user()' method before calling methods dependent on a user context";
}