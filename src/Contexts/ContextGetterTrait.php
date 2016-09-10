<?php

namespace Halfpastfour\Reddit\Contexts;

use Halfpastfour\Reddit\Reddit;

/**
 * Class ContextGetterTrait
 * @package Halfpastfour\Reddit\Contexts
 * @property Reddit $client
 */
trait ContextGetterTrait
{
	/**
	 * Should return an array with the all available contexts containing the fields 'subreddit', 'user', 'private_message'
	 *  and 'thing'
	 *
	 * @return array
	 */
	public function getContext()
	{
		return [
			'private_message'	=> $this->getPrivateMessageContext(),
			'subreddit'			=> $this->getSubredditContext(),
			'thing'				=> $this->getThingContext(),
			'user'				=> $this->getUserContext(),
		];
	}

	/**
	 * @return string|null
	 */
	public function getPrivateMessageContext()
	{
		return $this->client->privateMessageContext;
	}

	/**
	 * @return string|null
	 */
	public function getSubredditContext()
	{
		return $this->client->subredditContext;
	}

	/**
	 * @return string|null
	 */
	public function getThingContext()
	{
		return $this->client->thingContext;
	}

	/**
	 * @return string|null
	 */
	public function getUserContext()
	{
		return $this->client->userContext;
	}
}