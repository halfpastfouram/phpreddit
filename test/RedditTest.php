<?php

Namespace Test;

use GuzzleHttp\Exception\RequestException;
use Halfpastfour\Reddit\ArrayOptions\Listing;
use Halfpastfour\Reddit\Contexts\Subreddit;
use Halfpastfour\Reddit\Contexts\User;
use Halfpastfour\Reddit\Reddit;
use Halfpastfour\Reddit\TokenStorageMethod;
use PHPUnit_Framework_TestCase;
use Prophecy\Exception\Exception;

/**
 * Class RedditTest
 * @package Test
 */
class RedditTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var Reddit
	 */
	protected $reddit;

	/**
	 *
	 */
	public function setUp()
	{
		// Import config
		/** @noinspection PhpIncludeInspection */
		$this->config = include( './config.php' );
		// Set up reddit
		$this->reddit = new Reddit(
			$this->config['username'],
			$this->config['password'],
			$this->config['id'],
			$this->config['secret']
		);

		// Set token storage method to FILE to avoid exceptions about headers already being sent
		$this->reddit->setTokenStorageMethod( TokenStorageMethod::FILE, 'phpreddit:token', 'reddit.token' );
		// Set the user agent
		$this->reddit->setUserAgent( $this->config['userAgent'] );
	}

	/**
	 *
	 */
	public function testInstance()
	{
		$this->assertTrue( $this->reddit instanceof Reddit, 'Logging in successful' );
	}

	/**
	 *
	 */
	public function testUserAgent()
	{
		$this->reddit->setUserAgent( $this->config['userAgent'] );

		$this->assertEquals( $this->reddit->getUserAgent(), $this->config['userAgent'], 'Setting user agent' );
	}

	/**
	 *
	 */
	public function testUserContext()
	{
		$result = $this->reddit->user( $this->config['username'] );
		$this->assertTrue( $result instanceof User, 'Getting user context' );
		$this->assertEquals( $result->getUserContext(), $this->config['username'], 'Checking user context' );
	}

	/**
	 *
	 */
	public function testSubredditContext()
	{
		$result = $this->reddit->subreddit( 'all' );
		$this->assertTrue( $result instanceof Subreddit, 'Getting subreddit context' );
		$this->assertEquals( $result->getSubredditContext(), 'all', 'Checking subreddit context' );
	}

	/**
	 *
	 */
	public function testClearContext()
	{
		$this->reddit->clearContext();
		$this->assertTrue( is_null( $this->reddit->subredditContext ), 'Subreddit context is null' );
		$this->assertTrue( is_null( $this->reddit->userContext ), 'User context is null' );
	}

	/**
	 *
	 */
	public function testPrivateMessages()
	{
		$this->reddit->clearContext();
		$limit		= rand( 10, 1000 );
		$resultSet	= $this->reddit->getPrivateMessages( $limit );
		$this->assertTrue( is_array( $resultSet ), 'Not empty result' );
		$this->assertEquals( count( $resultSet ), $limit, "Result limited to {$limit}" );
	}

	/**
	 *
	 */
	public function testGetComments()
	{
		$this->reddit->clearContext();

		// Test result set from /r/all
		$limit		= rand( 10, 1000 );
		$resultSet	= $this->reddit->getComments( 'all', $limit );
		$this->assertTrue( !empty( $resultSet ), 'Not empty result' );
		$this->assertEquals( count( $resultSet ), $limit, "Result limited to {$limit}" );

		// Empty result set
		$this->expectException( RequestException::class );
		$this->expectExceptionCode( 404 );
		$this->reddit->getComments( uniqid( 'non_existing_subreddit_' ) );
	}
}