<?php
namespace LukeNZ\Reddit\Contexts;

trait ContextSetterTrait {
	
	/**
     * Sets the user context for future method calls.
     *
     * @param   $user   The user to set the context for.
     * @return  Contexts\User
     */
    public function user($user) {
        return new Contexts\User($this->client, $user);
    }

    /**
     * Sets the subreddit context for future method calls.
     *
     * @param   $subreddit  The subreddit to set the context for.
     * @return  Contexts\Subreddit
     */
    public function subreddit($subreddit) {
        return new Contexts\Subreddit($this->client, $subreddit);
    }

    /**
     * Sets the thing context for future method calls.
     *
     * @param   $thing  The thing to set the context for.
     * @return  Contexts\Thing
     */
    public function thing($thing) {
        return new Contexts\Thing($this->client, $thing);
    }
}