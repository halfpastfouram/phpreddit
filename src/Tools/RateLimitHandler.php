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

namespace Halfpastfour\Reddit\Tools;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Halfpastfour\Reddit\Reddit;

/**
 * Class RateLimitHandler
 * @package Halfpastfour\Reddit\Tools
 */
class RateLimitHandler
{
	/**
	 * GuzzleHttp Client used for requests.
	 *
	 * @var Client
	 */
	protected $client;

	/**
	 * Array containing headers used for requests.
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * The amount of requests remaining until the rate limit is reached according to the last received response.
	 *
	 * @var int
	 */
	protected static $remainingRequests;

	/**
	 * The amount of requests that have been used up according to the last received response.
	 *
	 * @var int
	 */
	protected static $usedRequests;

	/**
	 * The amount of seconds until the limit is reset according to the last received response.
	 *
	 * @var int
	 */
	protected static $secondsUntilReset;

	/**
	 * The timestamp from the last request according to the last received response.
	 *
	 * @var int
	 */
	protected static $lastRequestTimestamp;

	/**
	 * The timestamp when the rate limit is next reset according to the last received response.
	 *
	 * @var int
	 */
	protected static $nextResetTimestamp;

	/**
	 * RateLimitHandler constructor.
	 *
	 * @param \GuzzleHttp\Client $client
	 * @param array              $headers
	 */
	public function __construct( Client $client, array $headers = [] )
	{
		$this->client = $client;
		$this->setHeaders( $headers );
	}

	/**
	 * Set the headers to use for the
	 *
	 * @param array $headers
	 *
	 * @return RateLimitHandler
	 */
	public function setHeaders( array $headers ) : RateLimitHandler
	{
		$this->headers = $headers;

		return $this;
	}

	/**
	 * Return an array of headers that will be used for the next request.
	 *
	 * @return array
	 */
	public function getHeaders() : array
	{
		return $this->headers;
	}

	/**
	 * Return the amount of remaining requests until the current rate limit is reached according to the last received
	 * response.
	 *
	 * @return int
	 */
	public function getRemainingRequests() : int
	{
		return intval( self::$remainingRequests );
	}

	/**
	 * Return the amount of used requests for the current rate limit according to the last received response.
	 *
	 * @return int
	 */
	public function getUsedRequests() : int
	{
		return intval( self::$usedRequests );
	}

	/**
	 * Get the timestamp of the last request according to the last received response.
	 *
	 * @return int
	 */
	public function getLastRequestTimestamp() : int
	{
		return intval( self::$lastRequestTimestamp );
	}

	/**
	 * Get the timestamp for the next time the limit will be reset according to the last received response.
	 *
	 * @return int
	 */
	public function getNextResetTimestamp() : int
	{
		return intval( self::$nextResetTimestamp );
	}

	/**
	 * Determine whether or not the current rate limit has been reached.
	 *
	 * @return bool
	 */
	public function isLimitReached() : bool
	{
		if( time() >= $this->getNextResetTimestamp() ) {
			return true;
		} else {
			return $this->getRemainingRequests() == 0;
		}
	}

	/**
	 * Get the amount of seconds until the rate limit will be reset according to the last received response.
	 *
	 * @return int
	 */
	public function getSecondsUntilLimitIsReset() : int
	{
		return $this->getNextResetTimestamp() - time() ?: 0;
	}

	/**
	 * Makes an OAuth request to Reddit's servers whilst .
	 *
	 * @param   string $method The method that the Reddit API expects to be used.
	 * @param   string $url    URL to send to.
	 * @param   array  $body   The body of the request.
	 *
	 * @return Response
	 */
	public function httpRequest( string $method, string $url, array $body = null ) : Response
	{
		// Check if we need to throttle
		if( !is_null( self::$lastRequestTimestamp ) && self::$remainingRequests == 0
			&& self::$nextResetTimestamp > time()
		) {
			print( "Sleeping for " . ( self::$nextResetTimestamp - time() ) );
			sleep( self::$nextResetTimestamp - time() );
		}

		$headersAndBody = [ 'headers' => $this->getHeaders() ];
		if( !is_null( $body ) ) {
			$headersAndBody	= array_merge( $headersAndBody, $body );
		}

		// Perform the request and return the response
		/** @var Response $response */
		$response        = $this->client->request( $method, Reddit::OAUTH_URL . $url, $headersAndBody );
		$responseHeaders = $response->getHeaders();

		self::$lastRequestTimestamp = strtotime( $responseHeaders['Date'][0] );
		self::$remainingRequests    = intval( $responseHeaders['x-ratelimit-remaining'][0] );
		self::$usedRequests         = intval( $responseHeaders['x-ratelimit-used'][0] );
		self::$secondsUntilReset    = intval( $responseHeaders['x-ratelimit-reset'][0] );
		self::$nextResetTimestamp   = time() + self::$secondsUntilReset;

		return $response;
	}
}