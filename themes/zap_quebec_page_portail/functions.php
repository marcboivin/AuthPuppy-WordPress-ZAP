<?php

add_filter('option_blogname', 'zap_filter_ZAP', 10, 1);

function zap_filter_ZAP($title){
	$title = preg_replace('/^ZAP( -|-|—| —)?/g', '', $title);
	
	return $title;
}