<?php

add_filter('option_blogname', 'zap_filter_ZAP', 10, 1);

/*
	emove ZAP from a title string
 */
function zap_filter_ZAP($title){
	$title = trim($title); // caus dave ain't always careful when typing names
	
	$title = preg_replace('/^ZAP( -|-|—| —)?/i', '', $title);
	
	return $title;
}