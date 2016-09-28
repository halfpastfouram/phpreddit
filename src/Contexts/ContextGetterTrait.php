<?php
/**
 * Copyright (c) 2016 halfpastfour.am
 * MIT
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

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
	public function getContext() : array
	{
		return [
			'private_message'	=> $this->getPrivateMessageContext(),
			'subreddit'			=> $this->getSubredditContext(),
			'thing'				=> $this->getThingContext(),
			'user'				=> $this->getUserContext(),
		];
	}

	/**
	 * Should return the current message context.
	 *
	 * @return string
	 */
	public function getPrivateMessageContext() : string
	{
		return strval( $this->client->privateMessageContext );
	}

	/**
	 * Should return the current subreddit context.
	 *
	 * @return string
	 */
	public function getSubredditContext() : string
	{
		return strval( $this->client->subredditContext );
	}

	/**
	 * Should return the current thing context.
	 *
	 * @return string
	 */
	public function getThingContext() : string
	{
		return strval( $this->client->thingContext );
	}

	/**
	 * Should return the current user context.
	 *
	 * @return string
	 */
	public function getUserContext() : string
	{
		return strval( $this->client->userContext );
	}
}