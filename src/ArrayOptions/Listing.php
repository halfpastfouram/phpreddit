<?php

namespace LukeNZ\Reddit\ArrayOptions;

class Listing
{
    private $shouldPaginate = false;

    private $after;
    private $before;

    private $count = 0;
    private $limit = 25;
    private $show;
    private $subredditDetail;

    public function isPaginating() {
        return ($this->shouldPaginate === 'increment' || $this->shouldPaginate === 'decrement');
    }

    public function setPaginationDirection($paginationDirection) {
        if ($paginationDirection === 'increment' || $paginationDirection === 'decrement') {
            $this->shouldPaginate = $paginationDirection;
        }
    }

    public function disablePagination() {
        $this->shouldPaginate = false;
    }

    public function getAfter() {
        return $this->after;
    }

    public function setAfter($after) {
        $this->after = $after;
    }

    public function getBefore() {
        return $this->before;
    }

    public function setBefore($before) {
        $this->before = $before;
    }

    public function setCount($count) {
        $this->count = $count;
    }

    public function setLimit($limit) {
        $this->limit = $limit;
    }

    public function enableShow() {
        $this->show = 'all';
    }

    public function disableShow() {
        $this->show = null;
    }

    public function enableSubredditDetail() {
        $this->subredditDetail = true;
    }

    public function disableSubredditDetail() {
        $this->subredditDetail = false;
    }

    /**
     * Output the listing for use in an API request.
     *
     * @return array    The listing as an array.
     */
    public function output() {
        if (isset($this->after)) {
            $output['after'] = $this->getAfter();
        } else if (isset($this->before)) {
            $output['before'] = $this->getBefore();
        }

        $output['limit'] = $this->limit;
        $output['count'] = $this->count;

        if ($this->show === 'all') {
            $output['show'] = 'all';
        }

        $output['sr_detail'] = $this->subredditDetail;

        return $output;
    }

    public function incrementPagination() {
        $this->count += $this->limit;
    }

    public function decrementPagination() {
        $this->count -= $this->limit;
    }
}