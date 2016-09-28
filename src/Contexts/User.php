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

use Halfpastfour\Reddit\HttpMethod;
use Halfpastfour\Reddit\Interfaces\Context;
use Halfpastfour\Reddit\Reddit;

/**
 * Class User
 * @package Halfpastfour\Reddit\Contexts
 */
class User implements Context
{
	use ContextSetterTrait;use ContextGetterTrait;


	/**
	 * @var Reddit
	 */
	protected $client;

	/**
	 * User constructor.
	 *
	 * @param Reddit $p_oClient
	 * @param string $id
	 */
	public function __construct( Reddit $p_oClient, string $id )
	{
		$this->client              = $p_oClient;
		$this->client->userContext = $id;
	}

	/**
	 * Fetch the submitted selfposts and links for the user in the current context.
	 *
	 * @param      $sort
	 * @param      $timeInterval
	 * @param      $afterThing
	 * @param      $beforeThing
	 * @param      $count
	 * @param      $limit
	 * @param bool $subredditDetail
	 *
	 * @return mixed
	 */
	public function submitted(
		$sort,
		$timeInterval,
		$afterThing,
		$beforeThing,
		$count,
		$limit,
		$subredditDetail = false
	) {
		$options['show']     = 'given';
		$options['sort']     = $sort;
		$options['t']        = $timeInterval;
		$options['username'] = $this->client->userContext;
		$options['after']    = $afterThing;
		$options['before']   = $beforeThing;
		$options['count']    = $count;

		if( !is_null( $limit ) ) {
			$options['limit'] = min( $options['count'], 100 );
		} else {
			$options['limit'] = 25;
		}

		$options['sr_detail'] = $subredditDetail;

		$response = $this->client->httpRequest(
			HttpMethod::POST,
			"api/{$this->client->userContext}/submitted",
			$options
		);

		return json_decode( $response->getBody()->getContents() );
	}

	/**
	 * @param array $options
	 */
	public function setFlair( array $options )
	{
	}

	/**
	 *
	 */
	public function deleteFlair()
	{
	}
}