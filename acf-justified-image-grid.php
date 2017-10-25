<?php

/*
Plugin Name: Advanced Custom Fields: Justified Image Grid
Plugin URI: https://dreihochzwo.de/wordpress-plugins/advanced-custom-fields-addon-justified-image-grid/
Description: Generates an image grid with the gallery field of ACF 5 Pro
Version: 1.2.0
Author: Thomas Meyer
Author URI: http://www.dreihochzwo.de
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/




// 1. set text domain
// Reference: https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
load_plugin_textdomain( 'acf-jig', false, dirname( plugin_basename(__FILE__) ) . '/lang/' ); 




// 2. Include field type for ACF5
// $version = 5 and can be ignored until ACF6 exists
function include_field_types_justified_image_grid( $version ) {
	
	include_once('acf-justified-image-grid-v5.php');
	
}

add_action('acf/include_field_types', 'include_field_types_justified_image_grid');	

?>