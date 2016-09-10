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
 * Class PrivateMessage
 * @package Halfpastfour\Reddit\Contexts
 */
class PrivateMessage implements Context
{
	use ContextSetterTrait;
	use ContextGetterTrait;

	/**
	 * @var Reddit A pointer to the reddit client.
	 */
	protected $client;

	/**
	 * PrivateMessage constructor.
	 *
	 * @param Reddit $client
	 * @param string $p_sId
	 */
	public function __construct( Reddit $client, $p_sId )
	{
		$this->client              				= $client;
		$this->client->privateMessageContext	= $p_sId;
	}

	/**
	 * Return the contents of the message.
	 *
	 * @return array|null
	 */
	public function read()
	{
		$response	= $this->client->httpRequest( HttpMethod::POST, '/api/read_message.json', [
			'id'	=> $this->client->privateMessageContext,
		] );

		$result		= @json_decode( $response, true )['data'];

		return $response ? $result : null;
	}

	/**
	 * Reply to the message.
	 *
	 * @param string $p_sThingName
	 * @param string $p_sMessage
	 *
	 * @return array|null
	 */
	public function reply( $p_sThingName, $p_sMessage )
	{
		$response = $this->client->httpRequest( HttpMethod::POST, '/api/comment.json', [
			'text'     => strval( $p_sMessage ),
			'thing_id' => $p_sThingName,
			'api_type' => 'json',
		] );

		$result	= json_decode( $response, true );

		return $response && isset( $result['json']['data']['things'][0]['data'] )
			? $result['json']['data']['things'][0]['data']
			: null;
	}
}