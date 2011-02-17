<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| Routes
| -------------------------------------------------------------------
| You can specify your assets based on the current URI. You can group
| assets together, give them different weights so they are displayed
| in whatever order you want. You can set files, or custom code. For
| css, you can also set the media type, and it supports LESS.js.
|
| Each route can inherit assets from other routes. You can specify
| a specific route, or set it to parent which will inherit up a 
| level. You can also set it to only inherit just css, or javascript
| or both. You can also tell it to exclude specific groups from that
| route, for instance you may want to have specific css for the 
| homepage, but use the same javascript as the default route. You
| could either set it to inherit all assets and exclude the group
| that contains the css you don't want to load, or you could just
| inherit the javascript.
|
| Please view the sample file.
|
*/

$config['routes'] = array(
	'all' => array(
		'css' => array(
			
		),
		'js' => array(
			
		)
	),
	'default' => array(
		'css' => array(
			
		),
		'js' => array(
			
		)
	)
);


/*
| -------------------------------------------------------------------
| Cache Busting
| -------------------------------------------------------------------
| Set the default cache value. If set to false, it will append a
| timestamp to any asset without cache set to true. If set to true
| you must set all assets to cache => false if you don't want them 
| cached.
|
*/

$config['default_cache'] = false;

