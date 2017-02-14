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

namespace Halfpastfour\Reddit;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use Halfpastfour\Reddit\ArrayOptions\Listing;
use Halfpastfour\Reddit\Contexts\Subreddit;
use Halfpastfour\Reddit\Contexts\Thing;
use Halfpastfour\Reddit\Contexts\User;
use Halfpastfour\Reddit\Exceptions\TokenStorageException;
use Halfpastfour\Reddit\Tools\RateLimitHandler;
use Halfpastfour\Reddit\Tools\RequestLimitHandler;
use Predis\Client as PredisClient;

/**
 * Class Reddit
 * @package Halfpastfour\Reddit
 */
class Reddit
{
	const ACCESS_TOKEN_URL = 'https://www.reddit.com/api/v1/access_token';

	const OAUTH_URL        = 'https://oauth.reddit.com/';

	/**
	 * @var $client       GuzzleClient
	 * @var $username     string
	 * @var $password     string
	 * @var $clientId     string
	 * @var $clientSecret string
	 */
	protected $client, $username, $password, $clientId, $clientSecret, $accessToken, $tokenType,
		$userAgent, $callback;

	/**
	 * @var int The method used for storing the oauth token.
	 */
	protected $tokenStorageMethod = TokenStorageMethod::COOKIE;

	/**
	 * @var string The storage key used for storing the oauth token.
	 */
	protected $tokenStorageKey = "phpreddit:token";

	/**
	 * @var string The name of the token storage file.
	 */
	protected $tokenStorageFile;

	/**
	 * @var $subredditContext      string The current subreddit context
	 * @var $userContext           string The current user context
	 * @var $privateMessageContext string The current private message context
	 * @var $thingContext          string The current thing context
	 */
	public $subredditContext, $userContext, $privateMessageContext, $thingContext;

	/**
	 * Reddit constructor.
	 *
	 * @param   string $username     The username of the user you wish to control.
	 * @param   string $password     The password of the user you wish to control.
	 * @param   string $clientId     Your application's client ID.
	 * @param   string $clientSecret Your application's client secret.
	 */
	public function __construct( $username, $password, $clientId, $clientSecret )
	{

		$this->username     = $username;
		$this->password     = $password;
		$this->clientId     = $clientId;
		$this->clientSecret = $clientSecret;

		$this->client = new GuzzleClient();
	}

	/**
	 * Sets the user context for future method calls.
	 *
	 * @param   string $user The user to set the context for.
	 *
	 * @return  User
	 */
	public function user( string $user ) : User
	{
		return new User( $this, $this->stripPrefixes( $user ) );
	}

	/**
	 * Sets the subreddit context for future method calls.
	 *
	 * @param   string $subreddit The subreddit to set the context for.
	 *
	 * @return  Subreddit
	 */
	public function subreddit( string $subreddit ) : Subreddit
	{
		return new Subreddit( $this, $this->stripPrefixes( $subreddit ) );
	}

	/**
	 * Sets the thing context for future method calls.
	 *
	 * @param   string $thing The thing to set the context for.
	 *
	 * @return  Thing
	 */
	public function thing( string $thing ) : Thing
	{
		return new Thing( $this, $thing );
	}

	/**
	 * Fetches the user currently logged and returns their data.
	 *
	 * @return mixed The user currently logged in.
	 */
	public function me()
	{
		$response = $this->httpRequest( HttpMethod::GET, 'api/v1/me' );

		return json_decode( $response->getBody()->getContents(), true );
	}

	/**
	 * Post a comment to a thing identified by the given thing name. Returns the created comment upon sucess.
	 *
	 * @param string $thingName
	 * @param string $reply
	 *
	 * @return array
	 */
	public function comment( string $thingName, string $reply ) : array
	{
		$response = $this->httpRequest( HttpMethod::POST, 'api/comment.json', [
			'text'     => strval( $reply ),
			'thing_id' => $thingName,
			'api_type' => 'json',
		] )->getBody()->getContents();

		$result = json_decode( $response, true );

		return $response && isset( $result['json']['data']['things'][0]['data'] )
			? $result['json']['data']['things'][0]['data']
			: [];
	}

	/**
	 * Request a list of private messages.
	 *
	 * @param int $limit
	 *
	 * @return array
	 */
	public function getPrivateMessages( int $limit = 25 ) : array
	{
		$listing = new Listing;
		$listing->setLimit( $limit );

		$requestLimitHandler = new RequestLimitHandler(
			$this->client,
			HttpMethod::GET,
			'message/inbox.json',
			$listing
		);

		return $requestLimitHandler->doHttpRequests( new RateLimitHandler( $this->client, $this->getHeaders() ) );
	}

	/**
	 * Request a list of unread private messages.
	 *
	 * @param int $limit
	 *
	 * @return array
	 */
	public function getUnreadPrivateMessages( int $limit = 25 ) : array
	{
		$listing = new Listing;
		$listing->setLimit( $limit );

		$requestLimitHandler = new RequestLimitHandler(
			$this->client,
			HttpMethod::GET,
			'message/unread.json',
			$listing
		);

		return $requestLimitHandler->doHttpRequests( new RateLimitHandler( $this->client, $this->getHeaders() ) );
	}

	/**
	 * Request a list of sent private messages.
	 *
	 * @param int $limit
	 *
	 * @return array
	 */
	public function getSentPrivateMessages( int $limit ) : array
	{
		$listing = new Listing;
		$listing->setLimit( $limit );

		$requestLimitHandler = new RequestLimitHandler(
			$this->client,
			HttpMethod::GET,
			'message/sent.json',
			$listing
		);

		return $requestLimitHandler->doHttpRequests( new RateLimitHandler( $this->client, $this->getHeaders() ) );
	}

	/**
	 * For a given Reddit permalink, returns the JSON comment.
	 *
	 * The permalink can either contain 'reddit.com' as a string or not, if it is present, it will be stripped off.
	 * This does not return the post associated with the comment.
	 *
	 * @param   string $permalink The permalink URL to the comment.
	 *
	 * @return  mixed               The comment that was asked for in JSON format.
	 */
	public function getComment( $permalink )
	{

		// Strip off the domain if it exists
		if( stripos( $permalink, 'reddit.com/' ) !== false ) {
			$permalink = substr( $permalink, stripos( $permalink, 'reddit.com/' ) + strlen( 'reddit.com/' ) );
		}

		$response = $this->httpRequest( HttpMethod::GET, "{$permalink}.json" )->getBody()->getContents();

		// Strip off the listings and return the comment only.
		return json_decode( $response, true )[1]->data->children[0];
	}

	/**
	 * For a given subreddit or list of subreddits, returns the comments.
	 *
	 * @param string|array $p_mSubreddit
	 * @param int          $limit
	 *
	 * @return array  The requested comments.
	 */
	public function getComments( $p_mSubreddit, int $limit = 100 ) : array
	{
		if( !is_array( $p_mSubreddit ) ) {
			$subreddits = [ strval( $p_mSubreddit ) ];
		} else {
			$subreddits = array_map( 'strval', $p_mSubreddit );
		}

		// Create the permalink
		$permalink = 'r/' . implode( '+', $subreddits ) . '/comments.json';

		$listing = new Listing;
		$listing->setLimit( $limit );

		$requestLimitHandler = new RequestLimitHandler(
			$this->client,
			HttpMethod::GET,
			$permalink,
			$listing
		);

		return $requestLimitHandler->doHttpRequests( new RateLimitHandler( $this->client, $this->getHeaders() ) );
	}

	/**
	 * Return the user agent
	 *
	 * @return string
	 */
	public function getUserAgent()
	{
		return $this->userAgent;
	}

	/**
	 * Sets the user agent string for the Reddit client instance.
	 *
	 * Not required, but recommended by Reddit's API guidelines to prevent ratelimiting. Unique and
	 * descriptive names encouraged. Spoofing browsers and bots disallowed.
	 *
	 * @param string $p_sUserAgent The user agent string to assign.
	 *
	 * @return Reddit
	 */
	public function setUserAgent( $p_sUserAgent )
	{
		$this->userAgent = strval( $p_sUserAgent );

		return $this;
	}

	/**
	 * Sets the way a Reddit OAuth Bearer token is stored.
	 *
	 * This defaults to TokenStorageMethod::COOKIE by default, but if serverside use is required, 'Redis' and 'FILE'
	 * are also available.
	 *
	 * @param int    $tokenStorageMethod The method used to store tokens.
	 * @param string $key                The key to store the token in.
	 * @param string $tokenStorageFile   If the chosen storage method is 'FILE', require a file to store the key at.
	 *
	 * @return Reddit
	 *
	 * @throws TokenStorageException
	 */
	public function setTokenStorageMethod( $tokenStorageMethod, $key = "phpreddit:token", $tokenStorageFile = null )
	{
		// If a storage method of file is chosen yet no file location is provided, throw a TokenStorageException
		if( $tokenStorageMethod === TokenStorageMethod::FILE && $tokenStorageFile === null ) {
			throw new TokenStorageException();
		}

		// Set the storage method and the chosen key name
		$this->tokenStorageMethod = $tokenStorageMethod;
		$this->tokenStorageKey    = $key;
		$this->tokenStorageFile   = $tokenStorageFile;

		// Allow for method chaining
		return $this;
	}

	/**
	 * @internal
	 *
	 * If a passed thing string contains a prefix remove it for use in Reddit's API.
	 *
	 * Accepts and cleans both subreddits ("/r/" and "r/") and users ("/u/" and "u/"). If the prefix is not present,
	 * the string remains untouched.
	 *
	 * @param   string $thing The thing to cleanse.
	 *
	 * @return  string          The cleansed string.
	 */
	private function stripPrefixes( $thing )
	{
		$prefixes = array( "r/", "/r/", "u/", "/u/" );

		return str_replace( $prefixes, "", $thing );
	}

	/**
	 * @internal
	 *
	 * Fetches the headers that should be included in all OAuth calls to Reddit.
	 *
	 * By default, includes just the authorization header. If the user agent is set (recommended), this
	 * is included too.
	 *
	 * @return  array The headers to include in an HTTP request to Reddit
	 */
	private function getHeaders()
	{
		$token = $this->getRedditToken();

		$headers = [
			'Authorization' => "{$token['tokenType']} {$token['accessToken']}",
		];

		if( isset( $this->userAgent ) ) {
			$headers['User-Agent'] = $this->userAgent;
		}

		return $headers;
	}

	/**
	 * Makes an OAuth request to Reddit's servers.
	 *
	 * @param   string $method The method that the Reddit API expects to be used.
	 * @param   string $url    URL to send to.
	 * @param   array  $body   The body of the request.
	 *
	 * @return Response
	 */
	public function httpRequest( $method, $url, $body = null )
	{
		$this->getRedditToken();
		$ratelimitHandler = new RateLimitHandler( $this->client, $this->getHeaders() );

		return $ratelimitHandler->httpRequest( $method, $url, $body );
	}

	/**
	 * @internal
	 *
	 * Retrieve the Reddit token in a storage-agnostic way.
	 *
	 * If the token does not exist, make sure it is created and stored.
	 *
	 * @return array    The array of strings containing the Reddit token.
	 */
	private function getRedditToken()
	{
		if( $this->tokenStorageMethod === TokenStorageMethod::COOKIE ) {

			if( !isset( $_COOKIE[ $this->tokenStorageKey ] ) ) {
				$this->requestRedditToken();
				// Set the cookie to expire in 60 minutes.
				setcookie( $this->tokenStorageKey, $this->tokenType . ':' . $this->accessToken, 60 * 59 + time() );
				// Make the cookie available for this request.
				$_COOKIE[ $this->tokenStorageKey ] = $this->tokenType . ':' . $this->accessToken;

				// Return the newly created token
				return [ 'tokenType' => $this->tokenType, 'accessToken' => $this->accessToken ];
			} else {
				// Fetch the cookie instead
				$tokenInfo = explode( ":", $_COOKIE[ $this->tokenStorageKey ] );

				return [ 'tokenType' => $tokenInfo[0], 'accessToken' => $tokenInfo[1] ];
			}
		} elseif( $this->tokenStorageMethod === TokenStorageMethod::REDIS ) {

			$redis = new PredisClient();
			if( !$redis->get( $this->tokenStorageKey ) ) {
				$this->requestRedditToken();
				// Set the key to expire in 60 minutes
				$redis->setex( $this->tokenStorageKey, 60 * 59, $this->tokenType . ':' . $this->accessToken );

				// Return the newly created token
				return [ 'tokenType' => $this->tokenType, 'accessToken' => $this->accessToken ];
			} else {
				// Fetch the Redis key instead
				$tokenInfo = explode( ":", $redis->get( $this->tokenStorageKey ) );

				return [ 'tokenType' => $tokenInfo[0], 'accessToken' => $tokenInfo[1] ];
			}
		} elseif( $this->tokenStorageMethod === TokenStorageMethod::FILE ) {

			// If the file does not exist, request a token
			if( !file_exists( $this->tokenStorageFile ) ) {
				$this->requestRedditToken();
				file_put_contents(
					$this->tokenStorageFile, $this->tokenType . ':' . $this->accessToken . ':' . ( time() + 60 * 59 )
				);

				// Return the newly created token
				return [ 'tokenType' => $this->tokenType, 'accessToken' => $this->accessToken ];
				// A file does exist, check if the token is still valid
			} else {
				$tokenInfo = explode( ':', file_get_contents( $this->tokenStorageFile ) );

				// The token is valid
				if( $tokenInfo[2] < time() ) {
					$this->requestRedditToken();
					file_put_contents(
						$this->tokenStorageFile,
						$this->tokenType . ':' . $this->accessToken . ':' . ( time() + 60 * 59 )
					);

					// Return the newly created token
					return [ 'tokenType' => $this->tokenType, 'accessToken' => $this->accessToken ];
				} else {
					return [ 'tokenType' => $tokenInfo[0], 'accessToken' => $tokenInfo[1] ];
				}
			}
		}

		return null;
	}

	/**
	 * @internal
	 *
	 * Request A Reddit Token
	 *
	 * If the client does not have a current valid OAuth2 token, fetch one here and set the values as properties
	 * on the Client.
	 */
	private function requestRedditToken()
	{
		$response = $this->client->post(
			Reddit::ACCESS_TOKEN_URL, array(
				'headers'     => [
					'User-Agent' => $this->userAgent,
				],
				'query'       => [
					[
						'client_id'     => $this->clientId,
						'response_type' => 'code',
						'state'         => bin2hex( openssl_random_pseudo_bytes( 10 ) ),
						'redirect_uri'  => 'http://localhost/reddit/test.php',
						'duration'      => 'permanent',
						'scope'         => 'save,modposts,identity,edit,flair,history,modconfig,modflair,modlog,'
							. 'modposts,modwiki,mysubreddits,privatemessages,read,report,submit,subscribe,vote,'
							. 'wikiedit,wikiread',
					],
				],
				'auth'        => [ $this->clientId, $this->clientSecret ],
				'form_params' => [
					'grant_type' => 'password',
					'username'   => $this->username,
					'password'   => $this->password,
				],
			)
		);

		$body = json_decode( $response->getBody() );

		$this->tokenType   = $body->token_type;
		$this->accessToken = $body->access_token;
	}

	/**
	 * Clears context of private message, subreddit, thing and user.
	 *
	 * @return Reddit
	 */
	public function clearContext()
	{
		$this->privateMessageContext = null;
		$this->subredditContext      = null;
		$this->thingContext          = null;
		$this->userContext           = null;

		return $this;
	}
}