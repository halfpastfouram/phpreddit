<?php
namespace LukeNZ\Reddit\Contexts;

use LukeNZ\Reddit\HttpMethod;

class User {

	use ContextSetterTrait;

	protected $client;

	public function __construct($client, $username) {
		$this->client = $client;
		$this->client->userContext = $username;
	}

    /**
     * Fetch the submitted selfposts and links for the user in the current context.
     *
     * @param $sort
     * @param $timeInterval
     * @param $afterThing
     * @param $beforeThing
     * @param $count
     * @param $limit
     * @param bool|false $subredditDetail
     * @return mixed
     */
    public function submitted($sort, $timeInterval, $afterThing, $beforeThing, $count, $limit, $subredditDetail = false) {
        $options['show'] = 'given';
        $options['sort'] = $sort;
        $options['t'] = $timeInterval;
        $options['username'] = $this->client->userContext;
        $options['after'] = $afterThing;
        $options['before'] = $beforeThing;
        $options['count'] = $count;

        if (!is_null($limit)) {
            $options['limit'] = min($options['count'], 100);
        } else {
            $options['limit'] = 25;
        }

        $options['sr_detail'] = $subredditDetail;

        $response = $this->client->httpRequest(HttpMethod::POST, "api/{$this->client->userContext}/submitted", $options);
        return json_decode($response->getBody());
    }

	public function setFlair(array $options) {		
	}

	public function deleteFlair() {		
	}
}