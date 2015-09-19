<?php

class Reddit {

    protected $client, $username, $password, $clientId, $clientSecret, $accessToken, $tokenType;

    const ACCESS_TOKEN_URL = 'https://www.reddit.com/api/v1/access_token';
    const OAUTH_URL = 'https://oauth.reddit.com/';

    /**
     * @param $username     The username of the user you wish to control.
     * @param $password     The password of the user you wish to control.
     * @param $clientId     Your application's client ID.
     * @param $clientSecret Your application's client secret.
     */
    public function __construct($username, $password, $clientId, $clientSecret) {

        $this->client = new GuzzleHttp\Client();

        if (!isset($_COOKIE['reddit_token'])) {
            $this->requestRedditToken();
        }
    }

    public function me() {
        return $this->httpRequest('GET', 'api/v1/me');
    }

    public function raw($method, $url) {

    }

    private function httpRequest($method, $url) {
        if (!isset($_COOKIE['reddit_token'])) {
            $this->requestRedditToken();
        }

        // Perform the request
        $response = $this->client->{$method}($url, array(
            'headers' => [
                'Authorization' => $this->token_type . ' ' . $this->access_token
            ]
        ));
    }

    private function requestRedditToken() {
        $response = $this->client->post('', array(
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
            'auth' => [$this->$clientId, $this->clientSecret],
            'form_params' => [
                'grant_type' => 'password',
                'username' => $this->$username,
                'password' => $this->$password
            ]
        ));

        $body = json_decode($response->getBody());
        $this->tokenType = $body->token_type;
        $this->accessToken = $body->access_token;

        setcookie('reddit_token', $this->tokenType . ':' . $this->accessToken, 60 * 60 + time());
    }
}