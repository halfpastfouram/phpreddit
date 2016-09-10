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

use Halfpastfour\Reddit\ArrayOptions\Listing;
use Halfpastfour\Reddit\HttpMethod;
use Halfpastfour\Reddit\Interfaces\Context;
use Halfpastfour\Reddit\Reddit;

/**
 * Class Subreddit
 * @package Halfpastfour\Reddit\Contexts
 */
class Subreddit implements Context
{
	use ContextSetterTrait;
	use ContextGetterTrait;

	/**
	 * @var Reddit A pointer to the Reddit api client;
	 */
	protected $client;

	/**
	 * Subreddit constructor.
	 *
	 * @param Reddit $p_oClient
	 * @param string $p_sId
	 */
	public function __construct( Reddit $p_oClient, $p_sId )
	{
		$this->client                   = $p_oClient;
		$this->client->subredditContext = $p_sId;
	}

	/**
	 * Returns a collection of new listing from the current subreddit.
	 *
	 * @param Listing $listing A Reddit 'Listing' type
	 *
	 * @return mixed
	 */
	public function newListings( Listing $listing )
	{
		$response = $this->client->httpRequest( HttpMethod::GET, "r/{$this->client->subredditContext}/new", [
				'query' => $listing->output(),
		] );

		return json_decode( $response, true );
	}

	/**
	 * Returns a list of Wiki pages from the current subreddit.
	 *
	 * @return mixed
	 */
	public function wikiPages()
	{
		$response = $this->client->httpRequest(
			HttpMethod::GET,
			"r/{$this->client->subredditContext}/wiki/pages"
		);

		return json_decode( $response, true );
	}

	/**
	 * Returns a Wiki page from the current subreddit.
	 *
	 * @param string $wikiPageName The page name from the subreddit wiki to retrieve.
	 *
	 * @return mixed
	 */
	public function wikiPage( $wikiPageName )
	{
		$response = $this->client->httpRequest(
			HttpMethod::GET,
			"r/{$this->client->subredditContext}/wiki/{$wikiPageName}"
		);

		return json_decode( $response, true );
	}

	/**
	 * Submits either a selfpost or a link to current subreddit.
	 *
	 * Expects an array of options to be passed through as the POST body:
	 * 'captcha' the user's response to the CAPTCHA challenge
	 * 'extension' extension used for redirects
	 * 'iden' the identifier of the CAPTCHA challenge
	 * 'kind' one of (link, self)
	 * 'resubmit' boolean value
	 * 'sendreplies' boolean value
	 * 'text' raw markdown text
	 * 'title' title of the submission. up to 300 characters long
	 * 'url' a valid URL
	 *
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function submit( array $options )
	{
		$options['api_type'] = 'json';
		$options['sr']       = $this->client->subredditContext;

		$response = $this->client->httpRequest( HttpMethod::POST, "api/submit", $options );

		return json_decode( $response, true )->json;
	}

	/**
	 * @param $flairType
	 */
	public function clearFlairTemplates( $flairType )
	{
	}

	/**
	 * @param $templateId
	 */
	public function deleteFlairTemplate( $templateId )
	{
	}

	/**
	 * @param array $options
	 */
	public function setFlairConfiguration( array $options )
	{
	}

	/**
	 * @param array $csv
	 */
	public function setFlairCSV( array $csv )
	{
	}

	/**
	 * Return an array with the names of the moderators of the current subreddit.
	 *
	 * @return array|null
	 */
	public function getMods()
	{
		$response = $this->client->httpRequest(
			HttpMethod::GET,
			"r/{$this->client->subredditContext}/about/moderators.json",
			[ 'api_type' => 'json' ]
		);

		$result = json_decode( $response, true );

		return $response && isset( $result['data']['children'] )
			? array_column( $result['data']['children'], 'name' )
			: null;
	}
}