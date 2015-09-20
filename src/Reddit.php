<?php
namespace LukeNZ\Reddit;

use GuzzleHttp\Client;

class Reddit {

    protected $client, $username, $password, $clientId, $clientSecret, $accessToken, $tokenType, $userAgent, $callback;

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
        }
    }

    public function me() {
        return $this->httpRequest(HttpMethod::GET, 'api/v1/me');
    }

    public function getComment($permalink) {

        // Strip off the domain if it exists
        if (stripos($permalink, 'reddit.com/') !== false) {
            $permalink  = substr($permalink, stripos($permalink, 'reddit.com/') + strlen('reddit.com/'));
        }

        $response = $this->httpRequest(HttpMethod::GET, Reddit::OAUTH_URL . $permalink . '.json');
    }

    public function raw($method, $url) {

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
     * @param   HttpMethod  $method The method that the Reddit API expects to be used.
     * @param   string      $url    URL to send to.
     */
    private function httpRequest(HttpMethod $method, $url) {
        if (!isset($_COOKIE['reddit_token'])) {
            $this->requestRedditToken();
        }

        // Perform the request and return the response
        return $this->client->{$method}(Reddit::OAUTH_URL . $url, array(
            'headers' => $this->getHeaders()
        ));
    }

    /**
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