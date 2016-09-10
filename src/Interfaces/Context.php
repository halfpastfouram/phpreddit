<?php

namespace Halfpastfour\Reddit\Interfaces;

use Halfpastfour\Reddit\Reddit;

/**
 * Interface Context
 * @package Halfpastfour\Reddit\Interfaces
 *
 * @property Reddit $client
 */
interface Context
{
	/**
	 * Context constructor.
	 *
	 * @param Reddit $p_oClient
	 * @param string $p_sId
	 */
	public function __construct( Reddit $p_oClient, $p_sId );

	/**
	 * Should return an array with the all available contexts containing the fields 'subreddit', 'user', 'private_message'
	 *  and 'thing'
	 *
	 * @return array
	 */
	public function getContext();

	/**
	 * @return string|null
	 */
	public function getPrivateMessageContext();

	/**
	 * @return string|null
	 */
	public function getSubredditContext();

	/**
	 * @return string|null
	 */
	public function getThingContext();

	/**
	 * @return string|null
	 */
	public function getUserContext();

}