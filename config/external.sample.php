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
			array('data' => 'css/reset.css', 'group' => 'head', 'weight' => 1, 'cache' => false)
		),
		'js' => array(
			array('data' => 'js/less-1.0.41.min.js', 'group' => 'head', 'weight' => 10),
			array('data' => 'js/modernizr-1.6.min.js', 'group' => 'head', 'weight' => 10),
			array('data' => 'js/jquery-1.5.min.js', 'group' => 'foot,jquery', 'weight' => 1),
		)
	),
	'default' => array(
		'css' => array(
			array('data' => 'css/interior.less', 'type' => 'less', 'group' => 'head,interior', 'weight' => 1, 'cache' => false)
		),
		'js' => array(
			array('data' => "js/login.js", 'group' => 'foot', 'weight' => 10, 'cache' => false),
			array('data' => "var _gaq = [['_setAccount', 'UA-XXXXX-X'], ['_trackPageview']];
				(function(d, t) {
					var g = d.createElement(t),
					s = d.getElementsByTagName(t)[0];
					g.async = true;
					g.src = ('https:' == location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
					s.parentNode.insertBefore(g, s);
				})(document, 'script');", 'type' => 'custom', 'group' => 'foot,google', 'weight' => 10)
		)
	),
	'home' => array(
		'css' => array(
			array('data' => 'css/home.less', 'type' => 'less', 'group' => 'head', 'weight' => 1, 'cache' => false)
		),
		'js' => array(
			array('data' => 'http://maps.google.com/maps/api/js?sensor=false', 'group' => 'foot,google', 'weight' => 5),
			array('data' => 'js/map.js', 'group' => 'foot,google', 'weight' => 5, 'cache' => false)
		),
		'inherit' => array(
			'default' => array('assets' => 'all', 'exclude' => 'interior')
		)
	),
	'admin' => array(
		'css' => array(
			array('data' => 'css/admin.less', 'type' => 'less', 'group' => 'head', 'weight' => 5, 'cache' => false)
		),
		'js' => array(
			array('data' => 'js/jquery-ui-1.8.9.custom.min.js', 'group' => 'jquery', 'weight' => 5)
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

$config['default_cache'] = true;

