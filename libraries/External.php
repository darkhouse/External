<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CodeIgniter External Asset Manager Class
 *
 * Easily manage your css and javascript files on a per page basis.
 *
 * @package			CodeIgniter
 * @subpackage		Libraries
 * @category		Libraries
 * @author			Adam Jackett
 * @license			http://www.gnu.org/licenses/gpl.html
 */

class CI_External {
	
	private $_routes;
	private $_default_cache = false;
	private $_assets = array();
	
	/**
	 * Constructor - Sets preferences
	 * 
	 * The constructor can be passed an array of config values
	 */
	public function __construct($config){
		$this->CI =& get_instance();
		
		if(!empty($config)) $this->initialize($config);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Initialize preferences
	 *
	 * @access	public
	 * @param	array $config 
	 * @return	void
	 */
	public function initialize($config){
		//get default cache setting
		if(isset($config['default_cache'])){
			$this->_default_cache = $config['default_cache'];
		}
		
		//get routes
		if(isset($config['routes'])){
			$this->_routes = $config['routes'];
			
			//assets in the all route are always added
			if(isset($this->_routes['all'])){
				$this->_process('all');
			}
			
			//set assets for the current uri
			//loop backwards through the uri to find the most specific route
			$uri = $this->CI->uri->segment_array();
			if(empty($uri)) $uri = array('home');
			
			$found = false;
			for($i = count($uri); $i > 0; $i--){
				$uri_string = implode('/', $uri);
				if(isset($this->_routes[$uri_string])){
					$found = true;
					$this->_process($uri_string);
				}
				array_pop($uri); //pop the last segment off for the next iteration
			}
			
			//if no route based assets were found, use the default
			if($found === false && isset($this->_routes['default'])){
				$this->_process('default');
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * set
	 *
	 * Set an array of assets
	 *
	 * @access	public
	 * @param	string $asset 
	 * @param	array $data 
	 * @return	void
	 */
	public function set($asset, $data){
		//if data isn't a multidimensional array, make it multidimensional
		if(isset($data['data'])) $data = array($data);
		
		foreach($data as $options){
			//get groups, or set to all by default
			$groups = (isset($options['group'])) ? explode(',', $options['group']) : array('all');
			
			//set asset type
			$options['asset'] = $asset;
			
			//set default type, weight and media (if asset type is css)
			if(!isset($options['type'])) $options['type'] = 'file';
			if(!isset($options['weight'])) $options['weight'] = 999;
			if(!isset($options['cache'])) $options['cache'] = $this->_default_cache;
			if(!isset($options['media']) && $asset == 'css') $options['media'] = 'all';
			if(!isset($options['rel']) && $asset == 'css') $options['rel'] = 'stylesheet';
			
			foreach($groups as $group){
				//if asset group doesn't exist, create it
				if(!isset($this->_assets[$group])) $this->_assets[$group] = array();
				
				//set asset in group
				$this->_assets[$group][] = $options;
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * get
	 *
	 * Get the assets filtered by asset type, groups and type. Set tab spaces
	 * for prettier code.
	 *
	 * @access	public
	 * @param	string $asset
	 * @param	string $groups
	 * @param	string $type
	 * @param	int $tabs
	 * @return	void
	 */
	public function get($asset='all', $groups='all', $type='all', $tabs=1){
		$temp_assets = array();
		
		//if groups is not an array, make it an array
		if(!is_array($groups)) $groups = explode(',', $groups);
		
		//if no type was specified, default to all
		if(!$type) $type = 'all';
		
		//if all is in group array, get all assets
		if(in_array('all', $groups)){
			foreach($this->_assets as $assets){
				$this->_get($assets, $asset, $type, $temp_assets);
			}
		} else {
			//only get assets if the group exists
			foreach($groups as $group){
				if(isset($this->_assets[$group])){
					$this->_get($this->_assets[$group], $asset, $type, $temp_assets);
				}
			}
		}
		
		//sort the assets by weight, higher weight means heavier, and therefore
		//further down the list
		$temp_assets = $this->_multisort($temp_assets, 'weight', SORT_ASC);
		
		//get the output for the available assets
		$output = $this->_fetch_assets($temp_assets);
		
		//echo the assets with the appropritate tabs for pretty code
		echo implode("\n".str_repeat("\t", $tabs), $output)."\n";
	}
	
	// --------------------------------------------------------------------
	
	//get appropriate output for each asset
	private function _fetch_assets($assets){
		$output = array();
		foreach($assets as $asset){
			switch($asset['asset']){
				case 'css':
					switch($asset['type']){
						case 'file':
							$output[] = '<link rel="'.$asset['rel'].'" href="'.$asset['data'].$this->_set_cache($asset).'" media="'.$asset['media'].'" type="text/css" />';
							break;
						case 'less':
							$output[] = '<link rel="stylesheet/less" href="'.$asset['data'].$this->_set_cache($asset).'" media="'.$asset['media'].'" type="text/css" />';
							break;
						case 'custom':
							$output[] = '<style type="text/css" media="'.$asset['media'].'">';
							$output[] = $asset['data'];
							$output[] = '</style>';
							break;
					}
					break;
					
				case 'js':
					switch($asset['type']){
						case 'file':
							$output[] = '<script src="'.$asset['data'].$this->_set_cache($asset).'" type="text/javascript"></script>';
							break;
						case 'custom':
							$output[] = '<script type="text/javascript">';
							$output[] = $asset['data'];
							$output[] = '</script>';
							break;
					}
					break;
			}
		}
		return $output;
	}
	
	// --------------------------------------------------------------------
	
	//set array of assets
	private function _set($asset, $data, $exclude=false){
		//make data multidimensional if it's not already
		if(isset($data['data'])) $data = array($data);
		
		foreach($data as $options){
			$allowed = true;
			
			//exclude certain asset groups
			if($exclude !== false){
				$exclude_groups = explode(',', $exclude);
				$groups = (isset($options['group'])) ? explode(',', $options['group']) : array();
				if(count($groups)){
					foreach($exclude_groups as $ex){
						if(in_array($ex, $groups)){
							$allowed = false;
							break;
						}
					}
				}
			}
			
			//if asset is not being excluded, add it to the list
			if($allowed) $this->set($asset, $options);
		}
	}
	
	// --------------------------------------------------------------------
	
	//get asset and check any filters
	private function _get($assets, $asset, $type, &$temp_assets){
		foreach($assets as $data){
			$success = true;
			
			//if asset is not the specified asset type, ignore it
			if($asset != 'all' && $asset != $data['asset']) $success = false;
			
			//if asset is not the specified type, ignore it
			if($type != 'all' && $type != $data['type']) $success = false;
			
			//if asset is approved, add it to the output list
			if($success !== false){
				$temp_assets[] = $data;
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	//recursively process the routes
	private function _process($uri_string, $assets='all', $exclude=false){
		//get data
		$data = $this->_routes[$uri_string];
		
		//if data is css, set it
		if(isset($data['css'])){
			$this->_set('css', $data['css'], $exclude);
		}
		
		//if data is javascript, set it
		if(isset($data['js'])){
			$this->_set('js', $data['js'], $exclude);
		}
		
		//if the inherit property is set, process the assets
		if(isset($data['inherit'])){
			foreach($data['inherit'] as $key => $options){
				//set default process options
				if(!isset($options['assets'])) $options['assets'] = 'all';
				if(!isset($options['exclude'])) $options['exclude'] = false;
				
				switch($key){
					//if inherit is set to parent, process assets from parent segment
					case 'parent':
						$uri = explode('/', $uri_string);
						array_pop($uri);
						$parent = implode('/', $uri);
						if(!empty($parent) && isset($this->_routes[$parent])){
							$this->_process($parent, $options['assets'], $options['exclude']);
						}
						break;
					//if inherit is a route, process assets from that route
					default:
						if(isset($this->_routes[$key])){
							$this->_process($key, $options['assets'], $options['exclude']);
						}
						break;
				}
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	//set cache
	private function _set_cache($asset){
		if(!in_array($asset['type'], array('file', 'less'))) return '';
		$append = (strpos($asset['data'], '?') !== false ? '&' : '?');
		return ($asset['cache'] === false ? $append.time() : '');
	}
	
	//sort multidimensional array, used for sorting assets by weight
	private function _multisort(){
		$args = func_get_args();
		$data = array_shift($args);
		foreach ($args as $n => $field) {
			if (is_string($field)) {
				$tmp = array();
				foreach ($data as $key => $row){
					$tmp[$key] = $row[$field];
				}
				$args[$n] = $tmp;
			}
		}
		$args[] = &$data;
		call_user_func_array('array_multisort', $args);
		return array_pop($args);
	}
	
}

// END External class