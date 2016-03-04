<?php
namespace LukeNZ\Reddit\Contexts;

use LukeNZ\Reddit\ArrayOptions\Listing;
use LukeNZ\Reddit\HttpMethod;

class Subreddit {

	use ContextSetterTrait;

	protected $client;

	public function __construct($client, $subreddit) {
		$this->client = $client;
		$this->client->subredditContext = $subreddit;
	}

    /**
     * Returns a collection of new listing from the current subreddit.
     *
     * @param Listing   $listing    A Reddit 'Listing' type
     *
     * @return mixed
     */
    public function newListings(Listing $listing) {
        $response = $this->client->httpRequest(HttpMethod::GET, "r/{$this->client->subredditContext}/new", [
            'query' => $listing->output()
        ]);
        return json_decode($response->getBody());
    }

    /**
     * Returns a list of Wiki pages from the current subreddit.
     * @return mixed
     */
    public function wikiPages() {
        $response = $this->client->httpRequest(HttpMethod::GET, "r/{$this->client->subredditContext}/wiki/pages");
        return json_decode($response->getBody());
    }

    /**
     * Returns a Wiki page from the current subreddit.
     *
     * @param string $wikiPageName  The page name from the subreddit wiki to retrieve.
     * @return mixed
     */
    public function wikiPage($wikiPageName) {
        $response = $this->client->httpRequest(HttpMethod::GET, "r/{$this->client->subredditContext}/wiki/{$wikiPageName}");
        return json_decode($response->getBody());
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
     * @return mixed
     */
    public function submit(array $options) {
        $options['api_type'] = 'json';
        $options['sr'] = $this->client->subredditContext;

        $response = $this->client->httpRequest(HttpMethod::POST, "api/submit", $options);
        return json_decode($response->getBody())->json;
    }

    public function clearFlairTemplates($flairType) {    	
    }

    public function deleteFlairTemplate($templateId) {    	
    }

    public function setFlairConfiguration(array $options) {    	
    }

    public function setFlairCSV(array $csv) {    	
    }

}