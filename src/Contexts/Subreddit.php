<?php
namespace LukeNZ\Reddit\Contexts;

use LukeNZ\Reddit\HttpMethod;
use LukeNZ\Reddit\Contexts\ContextSetter;

class Subreddit implements ContextSetter {

	protected $client, $subreddit;

	public function __construct($client, $subreddit, $otherContexts) {
		$this->client = $client;
		$this->subreddit = $subreddit;
		
		if (array_key_exists('username', $otherContexts)) {
			$this->username = $otherContexts['username'];
		}

		if (array_key_exists('thing', $otherContexts)) {
			$this->thing = $otherContexts['thing'];
		}
	}

	/**
     * Returns a list of Wiki pages from the current subreddit.
     */
    public function wikiPages() {
        $response = $this->client->httpRequest(HttpMethod::GET, "{$this->subreddit}/wiki/pages");
    }

    /**
     * Returns a Wiki page from the current subreddit.
     *
     * @param       $wikiPageName               The page name from the subreddit wiki to retrieve.
     */
    public function wikiPage($wikiPageName) {
        $response = $this->client->httpRequest(HttpMethod::GET, "{$this->subreddit}/wiki/page/{$wikiPageName}");
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
        $options['sr'] = $this->subreddit;

        $response = $this->client->httpRequest(HttpMethod::POST, "api/submit", $options);
        return $response->getBody();
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