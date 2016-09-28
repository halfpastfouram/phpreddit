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

namespace Halfpastfour\Reddit\ArrayOptions;

/**
 * Class Listing
 * @package Halfpastfour\Reddit\ArrayOptions
 */
class Listing
{
	/**
	 * @var bool
	 */
	private $shouldPaginate = false;

	/**
	 * @var string
	 */
	private $after;

	/**
	 * @var string
	 */
	private $before;

	/**
	 * @var int
	 */
	private $count = 0;

	/**
	 * @var int
	 */
	private $limit = 25;

	/**
	 * @var string|null
	 */
	private $show;

	/**
	 * @var
	 */
	private $subredditDetail;

	/**
	 * @return bool
	 */
	public function isPaginating() : bool
	{
		return ( $this->shouldPaginate === 'increment' || $this->shouldPaginate === 'decrement' );
	}

	/**
	 * @param $paginationDirection
	 *
	 * @return Listing
	 */
	public function setPaginationDirection( $paginationDirection ) : Listing
	{
		if( $paginationDirection === 'increment' || $paginationDirection === 'decrement' ) {
			$this->shouldPaginate = $paginationDirection;
		}

		return $this;
	}

	/**
	 * @return Listing
	 */
	public function disablePagination() : Listing
	{
		$this->shouldPaginate = false;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getAfter()
	{
		return $this->after;
	}

	/**
	 * @param string $after
	 *
	 * @return Listing
	 */
	public function setAfter( string $after ) : Listing
	{
		$this->after	= $after;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getBefore()
	{
		return $this->before;
	}

	/**
	 * @param string $before
	 *
	 * @return Listing
	 */
	public function setBefore( string $before ) : Listing
	{
		$this->before	= $before;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCount() : int
	{
		return intval( $this->count );
	}

	/**
	 * @param int $count
	 *
	 * @return Listing
	 */
	public function setCount( $count ) : Listing
	{
		$this->count = intval( $count );

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLimit() : int
	{
		return intval( $this->limit );
	}

	/**
	 * @param int $limit
	 *
	 * @return Listing
	 */
	public function setLimit( int $limit ) : Listing
	{
		$this->limit = $limit;

		return $this;
	}

	/**
	 * @return Listing
	 */
	public function enableShow() : Listing
	{
		$this->show = 'all';

		return $this;
	}

	/**
	 * @return Listing
	 */
	public function disableShow() : Listing
	{
		$this->show = null;

		return $this;
	}

	/**
	 * @return Listing
	 */
	public function enableSubredditDetail() : Listing
	{
		$this->subredditDetail = true;

		return $this;
	}

	/**
	 * @return Listing
	 */
	public function disableSubredditDetail() : Listing
	{
		$this->subredditDetail = false;

		return $this;
	}

	/**
	 * Output the listing for use in an API request.
	 *
	 * @return array The listing as an array.
	 */
	public function output() : array
	{
		$output	= [
			'limit'		=> $this->getLimit(),
			'count'		=> $this->getCount(),
		];

		if( $this->subredditDetail ) $output['sr_detail']	= $this->subredditDetail;
		if( $this->getBefore() ) $output['before']	= $this->getBefore();
		if( $this->getAfter() ) $output['after']	= $this->getAfter();

		if( $this->show === 'all' ) {
			$output['show'] = 'all';
		}

		return $output;
	}

	/**
	 * @return Listing
	 */
	public function incrementPagination() : Listing
	{
		$this->count += $this->limit;

		return $this;
	}

	/**
	 * @return Listing
	 */
	public function decrementPagination() : Listing
	{
		$this->count -= $this->limit;

		return $this;
	}
}