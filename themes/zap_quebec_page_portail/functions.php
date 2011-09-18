<?php

add_filter('option_blogname', 'zap_filter_ZAP', 10, 1);

define('ZAP_DEFAULT_PLUGINS', 'ententes-contenus/quoi_faire_a_qc');

zap_load_default_content(ZAP_DEFAULT_PLUGINS);
/*
	emove ZAP from a title string
 */
function zap_filter_ZAP($title){
	$title = trim($title); // caus dave ain't always careful when typing names
	
	// Remove the fugly, inconsistant ZAP, we replace it with our own in the markup
	$title = preg_replace('/^ZAP( -|-|—| —)?/i', '', $title);
	
	// Remove the stupid number identifiers
	$title = preg_replace('/( -|-|—| —|[0-9]| )+$/', '', $title);
	
	return $title;
}

/*
 * Load activates plugins so that the default content is shown 
 * on all portal pages
 * 
 *
*/
function zap_load_default_content($plugins){
	$plugins = explode($plugin, ',');
	foreach ($plugins as $plugin){
		$plugin = trim($plugin);
		
		$plugin_path = ABSPATH . 'wp-content/plugins/{$plugin}.php';
	  	activate_plugin($plugin_path);
	}
}

function zap_title(){
	if(function_exists('apz_hijack_title')){
		echo apz_hijack_title();
	}	
	else{
		wp_title( '|', true, 'right' );
	}
}