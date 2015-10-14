<?php
namespace LukeNZ\Reddit\Contexts;

interface ContextSetter {
	public function user($user);
	public function subreddit($subreddit);
	public function thing($thing);
}