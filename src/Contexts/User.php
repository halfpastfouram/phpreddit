<?php
namespace LukeNZ\Reddit\Contexts;

use LukeNZ\Reddit\HttpMethod;

class User {

	use ContextSetterTrait;

	protected $client;

	public function __construct($client, $username) {
		$this->client = $client;
		$this->client->userContext = $username;
	}

	public function setFlair(array $options) {		
	}

	public function deleteFlair() {		
	}
}