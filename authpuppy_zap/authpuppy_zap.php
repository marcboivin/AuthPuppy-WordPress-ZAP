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

include 'HTTPReST.php';

define('APZ_SERVER_URL', 'auth.zapquebec.org'); // Where is your auth server
define('APZ_SECURE', 'TRUE'); // Do we use HTTPS?
define('APZ_WS_PATH', 'ws'); // Where is the webservice? By default it's /ws/


class AuthPuppyNode
{
	var $secure = false;
	var $id = null; // The gateway ID
	var $server_address;
	var $ws_path;
	var $rest;
	var $node_info;
	
	function __constuct($id, $server_address, $ws_path, $secure=false){
		$this->ws_path = $ws_path;
		$this->server_address = $server_address;
		$this->id = $gw_id;
		$this->secure = $secure;
	}
	
	function get_online_users(){
		$this->fetch_node_info();
	}
	
	private function fetch_node_info(){
		$url = 'http';
		$url .= $this->secure ? 's' : '';
		$url .= '//' . $this->server_address . '/';
		$url .= $this->ws_path . '/';
		$url .= '?action=get&object_class=Node&object_id=' . $his->id;
		
		$this->rest = new RESTClient();
		$this->rest->createRequest($url);
		$this->rest->sendRequest();
		$output = $this->rest->getResponse();		
		
		echo $output;
		
	}
	
	// Take the gateway ID in and crete an AuthPuppyNode with the defined constant
	static public function create_from_constant($gw_id){
		return new AuthPuppyNode($gw_id, APZ_SERVER_URL, APZ_WS_PATH, APZ_SECURE);
	}
}

$node = AuthPuppyNode::create_from_constant('433');
$node->get_online_users();