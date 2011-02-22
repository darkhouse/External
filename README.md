With this system, you're able to set specific css and javascript resources to certain pages based on the URI. This is a complete rewrite from my [previous version](http://codeigniter.com/forums/viewthread/101236/) and addresses a number of issues, which I've explained below, after the usage documentation.

##Installation
I recommend you autoload the External library, but if not you can load it like any other library:

    $this->load->library('external');
    
##Usage

The intent is that the majority of the *magic* is done in the config file (application/config/external.php). I've provided a sample config that is taken straight from a project I'm working on, but it can look rather overwhelming at first glance, so let's break it down.

##Config

###Routes
The main item in the config is the routes. It is a multidimensional array that has settings for each route. Every route can have these keys, **css**, **js** and **inherit** (which I'll explain later). If you wanted to load a specific stylesheet for your products page, you could do this:

	$config['routes'] = array(
		'products' => array(
			'css' => array(
				array('data' => 'css/products.css', 'group' => 'head', 'weight' => 5)
			)
		)
	);

As you can see I've added an array with data, group, and weight keys. I'll explain these a little later. There are 3 special routes to be aware of. The first is **all**. The assets in this route will be loaded on every page along with any assets for that specific route. Let's say we want a css reset and jquery on every page, we would do this:

	'all' => array(
		'css' => array(
			array('data' => 'css/reset.css', 'group' => 'head', 'weight' => 1)
		).
		'js' => array(
			array('data' => 'js/jquery.js', 'group' => 'head', 'weight' => 5)
		)
	)
	
The next 2 special routes are **home** and **default**. The way the routing works is it takes the current URI string and checks to see if there are any assets for that page. If so it stops looking, but if not, it pops the last segment off and tries the resulting string until it runs out of segments. If the URI string is empty to begin with, it checks the **home** route. If after it's checked all routes and it hasn't found anything, it checks the **default** route.

###Data

Every asset requires a data key. This is what will be loaded into the browser, it can be either a file path (local or remote), or a code snippet.

###Group

Grouping assets is a very important feature. It allows you to place certain assets in one part of your file, and other assets elsewhere. For instance, you might want to load some assets in the <head> and others just before the &lt;/body&gt;. Consider this:
	
	'home' => array(
		'css' => array(
			array('data' => 'css/home.css', 'group' => 'head', 'weight' => 1)
		),
		'js' => array(
			array('data' => 'http://maps.google.com/maps/api/js?sensor=false', 'group' => 'foot,google', 'weight' => 5),
			array('data' => 'js/map.js', 'group' => 'foot,google', 'weight' => 5)
		)
	)
	
Here we've added the home.css file to the head group, and some google map code to both the foot group and the google group. You can specify more than one group for an asset by separating them by commas. This is useful for inheritance which I'll explain later.

###Weight

The order in which you write your assets in the config is not necessarily the order they will be output to the browser. You can use the weight key to specify the order you want everything to load. Keep in mind that weights are group specific. The idea behind weights is the higher the number, the lower the asset will be output, as it's heavier and *sinks* to the bottom of the group. Take this code:

	'all' => array(
		'css' => array(
			array('data' => 'css/reset.css', 'group' => 'head', 'weight' => 1)
		).
		'js' => array(
			array('data' => 'js/jquery.js', 'group' => 'head', 'weight' => 5)
		)
	)
	'home' => array(
		'css' => array(
			array('data' => 'css/home.css', 'group' => 'head', 'weight' => 3)
		),
		'js' => array(
			array('data' => 'http://maps.google.com/maps/api/js?sensor=false', 'group' => 'foot,google', 'weight' => 5),
			array('data' => 'js/map.js', 'group' => 'foot,google', 'weight' => 10)
		)
	)
	
Here, when our homepage is loading, and assuming it's outputing the head group separate from the foot group, it will output the reset.css file, then the home.css file and finally jquery.js in the head, in that order. It will then output the google maps api script, and the map.js file above the &lt;/body&gt;.

###Type

You're able to set the *type* of asset. This is required if you're using a custom code snippet, either with css or js, you would set the *type* to **custom**. If you were using a LESS css file, you could set the *type* to **less**. Here is how you would load some custom css:

	'products' => array(
		'css' => array(
			array('data' => '#header { background: url(images/bg_products.jpg) no-repeat; }', 'type' => 'custom' 'group' => 'head', 'weight' => 10)
		)
	)

###Media and Rel

For css files, you can set the *media* key in case you want to set a specific stylesheet for printing, a handheld device, or any other the other supported media values. The *rel* key allows you to set the rel attribute, which is also useful for LESS css (Note: you do not have to use both rel and type for LESS support. You can use either one, the result is the same.) 

###Cache

You can turn caching on or off for each asset. There is a default setting:

	$config['default_cache'] = true;
	
When set to true, it will cache all assets unless the asset has cache set to false. And vice versa, when the default value is set to false, it will not cache any assets unless cache is set to true for that asset. To bust the cache, we just need to append a unique value to the end of the asset path. To minimize the amount of requests the browser makes to the the files, our cachebusting is based on the file's modification time, so every time the file is updated it will append a new timestamp on the filepath.

###Inheritance

The default behaviour when processing the routes is to stop when it finds assets defined for a route. But sometimes you might want it to continue checking. You can tell it to inherit the parent route, or even a completely different route.

	'products' => array(
		'css' => array(
			array('data' => 'css/products.css', 'group' => 'head', 'weight' => 5)
		)
	),
	'products/shoes' => array(
		'css' => array(
			array('data' => 'css/shoes.css', 'group' => 'head', 'weight' => 7)
		),
		'inherit' => array(
			'parent' => array('assets' => 'all')
		)
	)
	
So when you're on the products/shoes page, it will get the shoes.css file in the products/shoes route and then inherit everything from the products route. And because the weight is higher for the shoes.css file it will output the products.css file first, and then the shoes.css file.

You can specify more than one route to inherit from. There are 2 special keys as well, **assets** and **exclude**. The **assets** key allows you to specify which assets to inherit, either *css*, *js* or *all*. Lets look at the **exclude** key used in the sample config:

	'default' => array(
		'css' => array(
			array('data' => 'css/interior.less', 'cache' => false, 'type' => 'less', 'group' => 'head,interior', 'weight' => 1)
		),
		'js' => array(
			array('data' => "js/login.js", 'group' => 'foot', 'weight' => 10),
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
			array('data' => 'css/home.less', 'cache' => false, 'type' => 'less', 'group' => 'head', 'weight' => 1)
		),
		'js' => array(
			array('data' => 'http://maps.google.com/maps/api/js?sensor=false', 'group' => 'foot,google', 'weight' => 5),
			array('data' => 'js/map.js', 'group' => 'foot,google', 'weight' => 5)
		),
		'inherit' => array(
			'default' => array('assets' => 'all', 'exclude' => 'interior')
		)
	)
	
Here we have the home route inheriting all assets from the default route, but excluding the interior group. Since it's a group parameter, you can specify more than one group to exclude, separated by commas. So when you're on the homepage, it is loading the home.less file but excluding the interior.less file. Essentially it's just inheriting the javascript specified in the default route. This could've also been done by doing this:

	'inherit' => array(
		'default' => array('assets' => 'js')
	)

##Setting Assets

You don't have to just rely on the config file for setting assets. There maybe instances where you need specific assets on a page and can't use the config file, such as if you need to pass something to a view and then want to set some custom style because of it. You can use the set method in controllers or in your views, I personally do it all in the view since it's output. The set method just takes 2 parameters, the asset type (css or js) and an array of options.

    $this->external->set('css', array(
        'data' => "#header { background-image: url($header_bg); }",
        'type' => 'custom',
        'group' => 'head',
        'weight' => 5
    ));

And when you want to get the assets, you can use the get method. It takes 4 optional parameters, asset (css, js, or all, default is all), group (this can be comma-separated or an array, default is all), type (if you want to get only a certain type of that asset, like all custom css, default is false) and tabs (to set the indentation for each line).

    $this->external->get('all', 'head', false, 2);
    
---
    
So that's it for usage. Here is a deeper explanation of all of the new features.

##Routing and Inheritance
The way the routing works is it takes the current URI string and checks to see if there are any files setup for it. If not, it drops the last segment and checks the resulting URI string until it runs out of segments. If it finds a config setting for that URI, it stops looking. But one of the things requested was inheritance. Some people thought that it should keep going and get the rest of the files from the parent(s), so I've added an inherit option. This allows you to inherit from the parent, or from a completely different route. 

Now sometimes there may be files that you don't want to inherit from a route, so I've also added an asset option and an exclude option. The default asset is 'all', but this can be set to 'js' or 'css' and it will only inherit those files. And the exclude option, the default is false, but if it's set to a group, or multiple groups, any assets that are part of those groups will not be inherited.

##Groups
I used Tony Dewan's [Carabiner](http://codeigniter.com/forums/viewthread/117966/) for a while, which I really like, he's done some great work. One feature it offered that made a lot of sense to me is groups. I've added this into External so that you can group certain assets together, such as a jquery group, or an IE group. You can set an asset to be part of multiple groups. This is useful when you want to output different sets of assets, and also for excluding assets during inheritance.

In the sample config I've created a few groups, the 2 main ones are head and foot which allows me to get all of the assets I want to load in the head, and then the other assets I want to load at the end of the page, such as google analytics.

##Asset Types and Media
This system is able to use different types of assets for both css and javascript. For css, it allows you to add css files, custom css snippets which will be wrapped in style tags, and also LESS in case you're using LESS.js (which I do). You can also specify the media, it's set to all by default, but you can specify any other media type you want, such as print. For javascript you can set files or custom javascript snippets which will be wrapped in script tags. If you do not specify a type, it will assume file by default.

##Weights
Another feature that was requested was the ability to output the assets in whatever order you want. The previous version had output the assets in its own order, and grouped certain types of assets together so that it always output javascript files before any custom javascript snippets, etc. But Now I've added a weight option to your assets, so when you call the get method, it sorts the assets by their assigned weight, allowing you to output the assets in any order you wish. Just remember that the higher the weight, the further down the list the asset will sink.

##Cachebusting
At the suggestion of CroNiX on the CI forum, I've added a cachebusting method with a default setting for all files in the config. Simply set any asset to cache => false and it will append a timestamp on the end of the file path. If the file path already has a ? in it, it will use a & instead.

##Tabs for Pretty Output
I've added a tabs option in the get method so that it will output the assets with the same indentation as the rest of your code. This is just for pretty markup, but another requested feature missing from the previous version.

##Conclusion
So that's the new External library. Although I think it's working very well, there is one thing it doesn't do. It doesn't minify any assets, like Carabiner does. I deliberately left this out because I think it would conflict with the weighting system, however there's nothing stopping you from using individually minified css and javascript files.