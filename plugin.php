<?php
/*
Plugin Name: X-Editable (ACF)
Plugin URI: https://github.com/wells5609/X-Editable-ACF
Description: In-line editing of Advanced Custom Fields (from the front-end) using X-Editable. Extends functionality of a base class that works with native WordPress post/user meta.
Version: 0.4.2
Author: wells
License: GPL
Copyright: wells 2013
*/

// Plugin setup
function x_editable_plugin() {

	define('X_EDITABLE_PATH', plugin_dir_path(__FILE__));
	define('X_EDITABLE_URL', plugins_url('', __FILE__));

	require_once X_EDITABLE_PATH . 'inc/plugin-loader.php';
}

add_action( 'init', 'x_editable_plugin' );

?>