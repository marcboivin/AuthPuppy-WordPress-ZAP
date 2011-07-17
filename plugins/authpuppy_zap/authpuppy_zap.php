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
define('APZ_SECURE', 'TRUE'); // Do we use HTTPS?
define('APZ_WS_PATH', 'ws'); // Where is the webservice? By default it's /ws/


global $Cache_Lite;
// Set a few options
$options = array(
    'cacheDir' => '/tmp/',
    'lifeTime' => 180,
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
	
	function __construct($id, $server_address, $ws_path, $secure=false){
		global $Cache_Lite;
		
		$this->ws_path = $ws_path;
		$this->server_address = $server_address;
		$this->id = $id;
		$this->secure = $secure;
		
		$this->cache = $Cache_Lite;
	}
	
	function OnlineUsers(){
		$this->fetch_node_info();
		$nb = $this->node_info->NumOnlineUsers ? $this->node_info->NumOnlineUsers : 0;
		
		return $nb;
	}
	
	private function fetch_node_info(){
		if($this->node_info)
			return true; // True means, infos are there leave the request alone
		$url = 'http';
		$url .= $this->secure ? 's' : '';
		$url .= '://' . $this->server_address . '/';
		$url .= $this->ws_path . '/';
		$url .= '?action=get&object_class=Node&object_id=' . $this->id;
		
		$request = new HTTP_Request2(
	        'http://rapidshare.com/cgi-bin/rsapi.cgi?sub=nextuploadserver_v1'
	    );
	    $server  = $request->send()->getBody();
	 
		$this->rest = new HTTP_Request2($url, HTTP_Request2::METHOD_GET);
		$output = $this->rest->send()->getBody();

		
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
		return new AuthPuppyNode($node_id, APZ_SERVER_URL, APZ_WS_PATH, APZ_SECURE);
	}
	
	static public function GetNode($node_id){
		global $Cache_Lite;
		
		// Check for cached objects
		if($object = $Cache_Lite->get($node_id)){
			return $object;
		}
		
		// No cache, we fetch the object and save it in cache
		$object = AuthPuppyNode::CreateFromConstant($node_id);
		
		print_r($object);
		
		$Cache_Lite->save($object, $node_id);
		
		return $object;
		
		
	}
}

$node = AuthPuppyNode::GetNode('433');

echo $node->OnlineUsers();