<?php
namespace LukeNZ\Reddit\Contexts;

use LukeNZ\Reddit\HttpMethod;
use LukeNZ\Reddit\Contexts\ContextSetter;

class Thing implements ContextSetter {

	protected $client, $thing;

	public function __construct($client, $thing, $otherContexts) {
		$this->client = $client;
		$this->thing = $thing;

		if (array_key_exists('username', $otherContexts)) {
			$this->username = $otherContexts['username'];
		}

		if (array_key_exists('subreddit', $otherContexts)) {
			$this->thing = $otherContexts['subreddit'];
		}
	}

	/**
      * Stickies the current post in the thread context.
      * 
      * Semantic alias for setSubredditSticky(true, $num);
      * 
      * @param integer $num 
      * @return mixed
      */
    public function stickyPost($num) {
        return $this->setSubredditSticky(true, $num);
    }

    /**
     * Unstickies the current post in the thread context, if it is stickied.
     * 
     * Semantic alias for setSubredditSticky(false);
     * 
     * @return mixed
     */
    public function unstickyPost() {
        return $this->setSubredditSticky(false);
    }

    /**
     * For a given post in the thread context, either stickies it or unstickies it
     * based on the boolean $state argument in the $num'th position (either 1 or 2).
     * 
     * Direct one to one mapping with the "api/set_subreddit_sticky" Reddit call.
     * 
     * @param boolean $state 
     * @param integer $num 
     * @return mixed
     */
    public function setSubredditSticky($state, $num = null) {
        $options['api_type'] = 'json';
        $options['id'] = $this->thing;
        $options['state'] = $state;

        if (!is_null($num)) {
            $optioms['num'] = $num;
        }

        $response = $this->client->httpRequest(HttpMethod::POST, "api/set_subreddit_sticky", $options);
        return $response->getBody();
    }

    /**
     * Semantic alias of editUserText for comments.
     *
     * @param string $text
     * @return mixed
     */
    public function editComment($text) {
        return $this->editusertext($text);
    }

    /**
     * Semantic alias of editUserText for selfposts.
     *
     * @param string $text
     * @return mixed
     */
    public function editSelfPost($text) {
        return $this->editusertext($text);
    }

    /**
     * Edit the body text of a comment or self-post.
     *
     * Accepts a parameter containing the raw markdown text to update the thing with. Expects a
     * thing exists as a context before being called.
     *
     * @param $text
     * @return mixed
     */
    public function editUserText($text) {
        $options['api_type'] = 'json';
        $options['thing_id'] = $this->thing;
        $options['text'] = $text;

        $response = $this->client->httpRequest(HttpMethod::POST, "api/editusertext", $options);
        return $response->getBody();
    }

    public function setFlair(array $options) {		
	}
}