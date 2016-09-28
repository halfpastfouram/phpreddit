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
use Halfpastfour\Reddit\ArrayOptions\Listing;
use Zend\Json\Json;

/**
 * Class RequestLimitHandler
 * @package Halfpastfour\Reddit\Tools
 */
class RequestLimitHandler
{
	/**
	 * @var \GuzzleHttp\Client
	 */
	protected $client;

	/**
	 * @var string
	 */
	protected $method;

	/**
	 * @var string
	 */
	protected $apiEndpoint;

	/**
	 * @var Listing
	 */
	protected $listing;

	/**
	 * @var int
	 */
	protected $limit;

	/**
	 * @var int
	 */
	protected $maxRetries	= 3;

	/**
	 * RequestLimitHandler constructor.
	 *
	 * @param Client  $client      The client used for requests.
	 * @param string  $method      The method used for requests.
	 * @param string  $apiEndpoint The api endpoint of the request.
	 * @param Listing $listing     The listing used for the request.
	 */
	public function __construct(
		Client $client,
		string $method,
		string $apiEndpoint,
		Listing $listing
	) {
		$this->client		= $client;
		$this->method		= $method;
		$this->apiEndpoint	= $apiEndpoint;
		$this->listing		= $listing;

		$this->setLimit( $listing->getLimit() );
	}

	/**
	 * Set the limit of the total items that will be requested.
	 *
	 * @param int $limit The total number of items that will be requested.
	 *
	 * @return RequestLimitHandler
	 */
	public function setLimit( int $limit ) : RequestLimitHandler
	{
		$this->limit = $limit;

		return $this;
	}

	/**
	 * Return the limit used for the request.
	 *
	 * @return int
	 */
	public function getLimit() : int
	{
		return intval( $this->limit );
	}

	/**
	 * Set the maximum number of retries in case a request has failed.
	 *
	 * @param int $maxRetries The number of allowed retries.
	 *
	 * @return RequestLimitHandler
	 */
	public function setMaxRetries( int $maxRetries ) : RequestLimitHandler
	{
		$this->maxRetries	= $maxRetries;

		return $this;
	}

	/**
	 * Perform requests until the configured limit is reached.
	 *
	 * @param RateLimitHandler $rateLimitHandler
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function doHttpRequests( RateLimitHandler $rateLimitHandler ) : array
	{
		$retries			= 0;
		$limitReached		= false;
		$resultCount		= 0;
		$resultData			= [];

		while( $resultCount < $this->getLimit() && !$limitReached ) {
			try {
				$response = $rateLimitHandler->httpRequest( $this->method, $this->apiEndpoint, [
					'query' => $this->listing->output()
				] );

				$responseData	= Json::decode( $response->getBody()->getContents(), Json::TYPE_ARRAY );

				if( $responseData ) {
					// Keep track of the total number of children
					$resultCount	+= count( $responseData['data']['children'] );
					// Push new children to the data array
					$resultData	= array_merge( $resultData,  $responseData['data']['children'] );

					// Update the "after" field in the listing
					if( $responseData['data']['after'] ) {
						$this->listing->setAfter( $responseData['data']['after'] );
					} else {
						$this->listing->setAfter( '' );
					}

					if( !$this->listing->getAfter() ) {
						// After limit reached, no more items left!
						$limitReached = true;
					}
				}
			} catch ( \Exception $exception ) {
				if( $exception->getCode() == 503 ) {
					if( $retries < $this->maxRetries ) {
						$retries++;
					} else {
						// Maximum retries reached.
						return $resultData;
					}
				} else {
					throw $exception;
				}
			}
		}

		return $resultData;
	}
}