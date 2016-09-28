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

namespace Halfpastfour\Reddit\Interfaces;

use Halfpastfour\Reddit\Reddit;

/**
 * Interface Context
 * @package Halfpastfour\Reddit\Interfaces
 *
 * @property Reddit $client
 */
interface Context
{
	/**
	 * Context constructor.
	 *
	 * @param Reddit $p_oClient
	 * @param string $id
	 */
	public function __construct( Reddit $p_oClient, string $id );

	/**
	 * Should return an array with the all available contexts containing the fields 'subreddit', 'user',
	 * 'private_message' and 'thing'
	 *
	 * @return array
	 */
	public function getContext() : array;

	/**
	 * Should return the current private message context.
	 *
	 * @return string
	 */
	public function getPrivateMessageContext() : string;

	/**
	 * Should return the current subreddit context.
	 *
	 * @return string
	 */
	public function getSubredditContext() : string;

	/**
	 * Should return the current thing context.
	 *
	 * @return string
	 */
	public function getThingContext() : string;

	/**
	 * Should return the current user context.
	 *
	 * @return string
	 */
	public function getUserContext() : string;

}