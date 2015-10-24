<?php
namespace LukeNZ\Reddit\Contexts;

use LukeNZ\Reddit\HttpMethod;

class User {

	use ContextSetterTrait;

	protected $client, $user;

	public function __construct($client, $username, $otherContexts) {
		$this->client = $client;
		$this->user = $username;
		
		if (array_key_exists('subreddit', $otherContexts)) {
			$this->subreddit = $otherContexts['subreddit'];
		}

		if (array_key_exists('thing', $otherContexts)) {
			$this->thing = $otherContexts['thing'];
		}
	}

	public function setFlair(array $options) {		
	}

	public function deleteFlair() {		
	}
}