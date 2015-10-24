# A clean, expressive API wrapper for Reddit, written in PHP

Created simply because nothing else out there worked. Use by requiring in the master dev stream in your `composer.json`:

    require: {
    	"luke-nz/phpreddit": "dev-master@dev"
    }

then...

    composer require luke-nz/phpreddit


## OAuth2 Integration

Simply pass in the user you wish to control and your Reddit ID and Reddit Secret:

    use LukeNZ\Reddit;

    $reddit = new Reddit('username', 'password', 'id', 'key');

Then set your user agent:

    $reddit->setUserAgent('My awesome bot!');

## Expressive, chainable syntax

Write better code by taking advantage of method chaining to retrieve data:

    $reddit->subreddit('spacex')->wikiPage('index');

## Extra sugar where available

Semantic mapping of endpoints improves your code's readability:

    $reddit->thing('thing_id')->editComment('some new text');

    // or

    $reddit->thing('thing_id')->editSelfPost('some new text');

Behind the scenes PHPReddit maps both of the above to the same API call (`api/editusertext`).

Of course, if you would prefer a closer one to one mapping with Reddit's API, the original methods can
still be used.

## Current Endpoints

Shown below is a list of API endpoints and how they are implemented by phpreddit, with the columns referring
to what class the methods can be called from.

| API endpoints | `Reddit` | `Subreddit`  | `User` | `Thing` |
|---|---|---|---|---|
| me (/api/v1/me) | me |  |  |  |
| getComment (/{$permalink}.json)| getComment |  |  |  |
| wikiPages (/{$subreddit}/wiki/pages) |  | wikiPages |  |  |
| wikiPage (/{$subreddit}/wiki/page/{$page}) |  | wikiPage |  |  |
| submit (/api/submit) |  | submit |  |  |
| editUserText (/api/editusertext) |  |  |  | editComment, editSelfpost, editUserText |
| setSubredditSticky (/api/set_subreddit_sticky) |  |  |  | stickyPost, unstickyPost, setSubredditSticky |

Used in upcoming project, eventually will support all endpoints and other features such as appending/prepending, etc.