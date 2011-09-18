<?php
/**
 * @package AuthPuppy ZAP
 */
/*
Plugin Name: AuthPuppy ZAP
Plugin URI: http://zapquebec.org
Description: Interagir avec Authpuppy en utilisant les pratiques de WordPress
Version: 0.1.0
Author: Marc boivin
Author URI: http://sobremarc.com
License: GPLv2 or later
*/

include 'HTTP/Request2.php'; // Pear module install with pear install HTTP_Request2-beta
require_once('Cache/Lite.php'); // Pear module install with pear install Cache_Lite

define('APZ_SERVER_URL', 'auth.zapquebec.org'); // Where is your auth server
define('APZ_SECURE', FALSE); // Do we use HTTPS?
define('APZ_WS_PATH', 'ws'); // Where is the webservice? By default it's /ws/

// If we initialize before that, we fubar the whole thing
add_action('wp_head', 'apz_init');


global $Cache_Lite;
// Set a few options
$options = array(
    'cacheDir' => '/tmp/',
    'lifeTime' => 900,
	'automaticSerialization' => true	
);

// Create a Cache_Lite object
$Cache_Lite = new Cache_Lite($options);

class AuthPuppyNode
{
	var $secure = false;
	var $id = null; // The gateway ID
	var $server_address;
	var $ws_path;
	var $rest;
	var $node_info = false;
	var $cache;
	var $in_error = false; // If we set in_error to true, we will prevent more request to the server
	var $fetcher_object = null; // Ususally HTTP_Request2, but it could be a mock for testing
	
	function __construct($fetcher_object, $id, $server_address, $ws_path, $secure=false){
		global $Cache_Lite;
		
		$this->fetcher_object = $fetcher_object;
		$this->ws_path = $ws_path;
		$this->server_address = $server_address;
		$this->id = $id;
		$this->secure = $secure;
		
		$this->cache = $Cache_Lite;
	}
	
	function online_users(){
		$this->fetch_node_info();
		
		$nb = $this->node_info->NumOnlineUsers > 0 ? $this->node_info->NumOnlineUsers : 0;
		
		return $nb;
	}
	
	function title(){
		$this->fetch_node_info();
		
		if( !empty( $this->node_info->Name ) ){
			return $this->node_info->Name;
		} else {
			return false;
		}
	}
	
	private function fetch_node_info(){
		if($this->node_info || $this->in_error)
			return true; // True means: infos are there leave the request alone
		$url = 'http';
		$url .= $this->secure ? 's' : '';
		$url .= '://' . $this->server_address . '/';
		$url .= $this->ws_path . '/';
		$url .= '?action=get&object_class=Node&object_id=' . $this->id;
		try {
			$this->fetcher_object->setMethod('GET');
			$this->fetcher_object->setUrl($url);

			$this->rest = $this->fetcher_object->send();
			$output = $this->rest->getBody();
		} catch (Exception $e) {
			$this->in_error = true;
			return false;
		}

		
		$json = json_decode($output);
		
		if($json->result != 1){
			return false;
		}
		// Store the values in the object
		$this->node_info = $json->values;
		
		// Save in cache
		$this->cache->save($this, $this->id);
		
	}
	
	// Take the gateway ID in and crete an AuthPuppyNode with the defined constant
	static public function CreateFromConstant($node_id){
		
		$r = new HTTP_Request2();
		
		return new AuthPuppyNode($r, $node_id, APZ_SERVER_URL, APZ_WS_PATH, APZ_SECURE);
	}
	
	static public function GetNode($node_id){
		global $Cache_Lite;
		
		// Check for cached objects
		if($object = $Cache_Lite->get($node_id)){
			return $object;
		}
		
		// No cache, we fetch the object and save it in cache
		$object = AuthPuppyNode::CreateFromConstant($node_id);
		
		$Cache_Lite->save($object, $node_id);
		
		return $object;
		
		
	}
}

function apz_get_current_node(){
	global $ap_node, $current_blog;
	
	// 404, maybe the blog just wasn't created. No excuses, we try and fetch it anyway!
	if(is_404()){
		$node_id = str_replace('/', '', $_SERVER['REQUEST_URI']);
	}else{
		$node_id = str_replace('/', '', $current_blog->path);	
	}
	
	$ap_node = AuthpuppyNode::GetNode($node_id);
}

function apz_init(){
	error_reporting(E_ALL);
	ini_set('display_errors','On');
	
	apz_get_current_node();
	
	add_action('option_blogname', 'apz_hijack_title', 1, 1);
	add_filter('wp_title', 'apz_hijack_title', 100);
	
}

/*
	Completly change the title of a site to the authpuppy value
*/
function apz_hijack_title($title){
	global $ap_node;
	// It breaks the loading, check it out.
	$node_title = $ap_node->title();
	
	if( $node_title ){
		return $node_title;
	}
	
	return $title;
}

function apz_connected_users(){
	global $ap_node;
	
	if ($ap_node){
		echo apz_get_connected_users($ap_node->id, $ap_node);
	}
}

function apz_get_connected_users($node_id, $node_object = null){
	if($node_object){
		return $node_object->online_users();
	}
	
	$node = AuthPuppyNode::GetNode($node_id);
	
	return $node->online_users();
	
}