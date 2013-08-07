<?php
/*
Plugin Name: X-Editable (ACF)
Plugin URI: https://github.com/wells5609/X-Editable-ACF
Description: Edit Advanced Custom Fields from the front-end using X-Editable. Extends a base class that works with native WordPress meta.
Version: 0.3.9
Author: Wells Peterson
License: GPL
Copyright: Wells Peterson
*/

// Plugin setup
function x_editable_plugin() {

	define('X_EDITABLE_PATH', plugin_dir_path(__FILE__));
	define('X_EDITABLE_URL', plugins_url('', __FILE__));

	require_once X_EDITABLE_PATH . 'inc/plugin-loader.php';
}

add_action( 'init', 'x_editable_plugin' );

?>