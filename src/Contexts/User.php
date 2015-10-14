<?php
namespace LukeNZ\Reddit\Contexts;

use LukeNZ\Reddit\HttpMethod;

class User {

	protected $client, $user;

	public function __construct($client, $username) {
		$this->client = $client;
		$this->user = $username;
	}
}