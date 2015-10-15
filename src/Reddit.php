<?php
namespace LukeNZ\Reddit;

use GuzzleHttp\Client;

use LukeNZ\Reddit\Contexts\User;
use LukeNZ\Reddit\Contexts\Subreddit;
use LukeNZ\Reddit\Contexts\Thing;
/**
 * Class Reddit
 * @package LukeNZ\Reddit
 */
class Reddit {

    /**
     * @var Client
     */
    protected $client, $username, $password, $clientId, $clientSecret, $accessToken, $tokenType, $userAgent, $callback;

    /**
     * @var
     */
    protected $subredditContext, $userContext, $thingContext;

    const ACCESS_TOKEN_URL = 'https://www.reddit.com/api/v1/access_token';
    const OAUTH_URL = 'https://oauth.reddit.com/';

    /**
     * @param   string    $username       The username of the user you wish to control.
     * @param   string    $password       The password of the user you wish to control.
     * @param   string    $clientId       Your application's client ID.
     * @param   string    $clientSecret   Your application's client secret.
     */
    public function __construct($username, $password, $clientId, $clientSecret) {

        $this->username = $username;
        $this->password = $password;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        $this->client = new Client();

        if (!isset($_COOKIE['reddit_token'])) {
            $this->requestRedditToken();
        } else {
            // Get cookie params
            $cookie = $_COOKIE['reddit_token'];

            $this->tokenType = explode(':', $cookie)[0];
            $this->accessToken = explode(':', $cookie)[1];
        }
    }

    /**
     * Sets the user context for future method calls.
     *
     * @param   $user   The user to set the context for.
     * @return  Contexts\User
     */
    public function user($user) {
        return new Contexts\User($this, $this->stripPrefixes($user));
    }

    /**
     * Sets the subreddit context for future method calls.
     *
     * @param   $subreddit  The subreddit to set the context for.
     * @return  Contexts\Subreddit
     */
    public function subreddit($subreddit) {
        return new Contexts\Subreddit($this, $this->stripPrefixes($subreddit));
    }

    /**
     * Sets the thing context for future method calls.
     *
     * @param   $thing  The thing to set the context for.
     * @return  Contexts\Thing
     */
    public function thing($thing) {
        return new Contexts\Thing($this, $thing);
    }

    /**
     * Fetches the user currently logged and returns their data.
     *
     * @return mixed    The user currently logged in.
     */
    public function me() {
        $response = $this->httpRequest(HttpMethod::GET, 'api/v1/me');
        return json_decode($response);
    }

    /**
     * For a given Reddit permalink, returns the JSON comment.
     *
     * The permalink can either contain 'reddit.com' as a string or not, if it is present, it will be stripped off.
     * This does not return the post associated with the comment.
     *
     * @param   string  $permalink  The permalink URL to the comment.
     * @return  mixed               The comment that was asked for in JSON format.
     */
    public function getComment($permalink) {

        // Strip off the domain if it exists
        if (stripos($permalink, 'reddit.com/') !== false) {
            $permalink  = substr($permalink, stripos($permalink, 'reddit.com/') + strlen('reddit.com/'));
        }

        $response = $this->httpRequest(HttpMethod::GET, "{$permalink}.json");

        // Strip off the listings and return the comment only.
        return json_decode($response->getBody())[1]->data->children[0];
    }

    /**
     * Sets the user agent string for the Reddit client instance.
     *
     * Not required, but recommended by Reddit's API guidelines to prevent ratelimiting. Unique and
     * descriptive names encouraged. Spoofing browsers and bots disallowed.
     *
     * @param   string  $userAgentString    The user agent string to assign.
     */
    public function setUserAgent($userAgentString) {
        $this->userAgent = $userAgentString;
    }

    /**
     * @internal
     *
     * If a passed thing string contains a prefix remove it for use in Reddit's API.
     *
     * Accepts and cleans both subreddits ("/r/" and "r/") and users ("/u/" and "u/"). If the prefix is not present,
     * the string remains untouched.
     *
     * @param   string  $thing  The thing to cleanse.
     * @return  string          The cleansed string.
     */
    private function stripPrefixes($thing) {
        $prefixes = array("r/", "/r/", "u/", "/u/");
        return str_replace($prefixes, "", $thing);
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
    private function getHeaders() {
        $headers = [
            'Authorization' => "{$this->tokenType} {$this->accessToken}"
        ];

        if (isset($this->userAgent)) {
            $headers['User-Agent'] = $this->userAgent;
        }

        return $headers;
    }

    /**
     * Makes an OAuth request to Reddit's servers.
     *
     * @param   string  $method The method that the Reddit API expects to be used.
     * @param   string  $url    URL to send to.
     */
    public function httpRequest($method, $url, $body = null) {
        if (!isset($_COOKIE['reddit_token'])) {
            $this->requestRedditToken();
        }

        $headersAndBody = array(
            'headers' => $this->getHeaders()
        );

        if (!is_null($body)) {
            $headersAndBody['form_params'] = $body;
        }

        // Perform the request and return the response
        return $this->client->{$method}(Reddit::OAUTH_URL . $url, $headersAndBody);
    }

    /**
     * @internal
     *
     * Request A Reddit Token
     *
     * If the client does not have a current valid OAuth2 token, fetch one here. Set it as a cookie.
     */
    private function requestRedditToken() {
        $response = $this->client->post(Reddit::ACCESS_TOKEN_URL, array(
            'query' => [
                [
                    'client_id' => $this->clientId,
                    'response_type' => 'code',
                    'state' => bin2hex(openssl_random_pseudo_bytes(10)),
                    'redirect_uri' => 'http://localhost/reddit/test.php',
                    'duration' => 'permanent',
                    'scope' => 'save,modposts,identity,edit,flair,history,modconfig,modflair,modlog,modposts,modwiki,mysubreddits,privatemessages,read,report,submit,subscribe,vote,wikiedit,wikiread'
                ]
            ],
            'auth' => [$this->clientId, $this->clientSecret],
            'form_params' => [
                'grant_type' => 'password',
                'username' => $this->username,
                'password' => $this->password
            ]
        ));

        $body = json_decode($response->getBody());
        $this->tokenType = $body->token_type;
        $this->accessToken = $body->access_token;

        // Set the cookie to expire in 60 minutes.
        setcookie('reddit_token', $this->tokenType . ':' . $this->accessToken, 60 * 60 + time());
    }
}