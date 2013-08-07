<?php

// Plugin class
class X_Editable_Plugin {
	
	public static $VERSION = '0.3.9.1';
	
	public static $XE_VERSION = '1.4.6';
		
	public static $EDIT_CAP = 'edit_posts';
	
	public static $FIELDS = array();
	
	public static $SCRIPTS_ENQUEUED = false;
		
	public $default_fields = array(
			'text',
			'number',
			'textarea',
			'select',
			'taxonomy',
			'date',
			'true_false',
			'user',
		);
		
	public $user_fields = array();
		
	public $default_user_field_path;
	
	
	function __construct() {		
	
		// Can user edit?
		self::$EDIT_CAP = apply_filters('xe/edit_cap', self::$EDIT_CAP);
		 
		// Constant must be set for fields to load
		if ( current_user_can(self::$EDIT_CAP) ) {
			define('XE_CAN_EDIT', true);
		}
		else {
			define('XE_CAN_EDIT', false);
		}
		
		// Plugins can also set this manually
		if ( current_theme_supports('x-editable-acf') ) {
			define('X_EDITABLE_ACF_ENABLE', true);
		}
		
		// user-registered fields
		$this->user_fields = apply_filters('xe/user_fields', $this->user_fields);
		
		// Default user field path is 'fields' directory of theme
		$this->default_user_field_path = apply_filters('xe/user_field_path', get_stylesheet_directory().'/fields/');
		
		require_once X_EDITABLE_PATH . 'fields/_base.php';
		
		require_once X_EDITABLE_PATH . 'inc/field-functions.php';
		
		require_once X_EDITABLE_PATH . 'inc/ajax-callbacks.php';
				
		require_once X_EDITABLE_PATH . 'inc/template-tag.php';
		
		
		if ( defined('X_EDITABLE_ACF_ENABLE') && X_EDITABLE_ACF_ENABLE ) {

			require_once X_EDITABLE_PATH . 'fields/x-editable-acf-field.php';
			
			// Add admin page if user has edit cap
			if ( XE_CAN_EDIT && is_admin() ) {
				include_once X_EDITABLE_PATH . 'views/admin.php';
			}
		}
		
		$this->register_scripts();
		$this->load_fields();
	}
		
	private function register_scripts() {
		
		// Bootstrap editable css
		wp_register_style('x-editable', X_EDITABLE_URL . '/assets/bootstrap-editable.min.css', array('bootstrap'), self::$XE_VERSION );
		// Bootstrap editable js
		wp_register_script('bs-editable', X_EDITABLE_URL . '/assets/bootstrap-editable.min.js', array('jquery', 'bootstrap-js'), self::$XE_VERSION, true );
		// X-Editable (ACF)
		wp_register_script('x-editable-js', X_EDITABLE_URL . '/assets/x-editable-acf.js', array('jquery', 'bs-editable'), self::$VERSION, true );
		
	}
	
	public static function enqueue_scripts() {
			
		if ( ! self::$SCRIPTS_ENQUEUED ) {
					
			wp_enqueue_style('x-editable');
			
			wp_enqueue_script('x-editable-js');
			wp_localize_script(	'x-editable-js', 'xeditable', array( 'ajaxurl' => network_admin_url('admin-ajax.php') ) );	
			
			self::$SCRIPTS_ENQUEUED = true;
		}

	}
	
	private function load_fields() {
		
		foreach($this->default_fields as $field) :
			
			$loc = X_EDITABLE_PATH . 'fields/' . $field . '.php';
			
			include_once $loc;
			
			self::$FIELDS[$field] = $loc;
			
		endforeach;
	
		foreach($this->user_fields as $field) :
			
			// default filename is {$fieldname}.field.php
			$default_location = $this->default_user_field_path . $field .'.php';
			
			$file_location = apply_filters('xe_' . $field . '_field_path', $default_location);
			
			if ( file_exists($file_location) ) {
				
				include_once $file_location;
				
				self::$FIELDS[$field] = $file_location;
			}
			
		endforeach;
	}
	
}

new X_Editable_Plugin;

?>