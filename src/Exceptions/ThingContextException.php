<?php
namespace LukeNZ\Reddit\Exceptions;

class ThingContextException extends \Exception {
    protected $message = "Please set a thing using the 'thing()' method before calling methods dependent on a thing context";
}