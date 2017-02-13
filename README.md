# A clean, expressive API wrapper for Reddit, written in PHP

Based on the work of LukeNz: [LukeNZ/phpreddit](https://github.com/LukeNZ/phpreddit)

[Read the API documentation here](https://halfpastfouram.github.io/phpreddit/)
-

Created simply because nothing else out there worked. Use by requiring in the master dev stream in your `composer.json`:

    require: {
    	"halfpastfouram/phpreddit": "dev-master@dev"
    }

then...

    composer require halfpastfouram/phpreddit


## OAuth2 Integration

Simply pass in the user you wish to control and your Reddit ID and Reddit Secret:

    use Halfpastfour\Reddit\Reddit;
    $reddit = new Reddit('username', 'password', 'id', 'key');

Then set your user agent:

    $reddit->setUserAgent('My awesome bot!');
