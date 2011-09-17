<?php

add_filter('option_blogname', 'zap_filter_ZAP', 10, 1);

define('ZAP_DEFAULT_PLUGINS', 'ententes-contenus/quoi_faire_a_qc')
/*
	emove ZAP from a title string
 */
function zap_filter_ZAP($title){
	$title = trim($title); // caus dave ain't always careful when typing names
	
	$title = preg_replace('/^ZAP( -|-|—| —)?/i', '', $title);
	
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