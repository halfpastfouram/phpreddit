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
    	$otherContexts = [];

    	if (isset($this->username)) {
    		$otherContexts['username'] = $this->username;
    	}

    	if (isset($this->thing)) {
    		$otherContexts['thing'] = $this->thing;
    	}

        return new Contexts\User($this->client, $otherContexts);
    }

    /**
     * Sets the subreddit context for future method calls.
     *
     * @param   $subreddit  The subreddit to set the context for.
     * @return  Contexts\Subreddit
     */
    public function subreddit($subreddit) {
    	$otherContexts = [];

    	if (isset($this->username)) {
    		$otherContexts['username'] = $this->username;
    	}

    	if (isset($this->thing)) {
    		$otherContexts['thing'] = $this->thing;
    	}

        return new Contexts\Subreddit($this->client, $subreddit, $otherContexts);
    }

    /**
     * Sets the thing context for future method calls.
     *
     * @param   $thing  The thing to set the context for.
     * @return  Contexts\Thing
     */
    public function thing($thing) {
    	$otherContexts = [];

    	if (isset($this->username)) {
    		$otherContexts['username'] = $this->username;
    	}

    	if (isset($this->thing)) {
    		$otherContexts['thing'] = $this->thing;
    	}

        return new Contexts\Subreddit($this->client, $subreddit, $otherContexts);
    }
}