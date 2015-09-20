<?php
namespace LukeNZ\Reddit\Exceptions;

class SubredditContextException extends \Exception {
    protected $message = "Please set a subreddit using the 'subreddit()' method before calling methods dependent on a subreddit context";
}