<?php

// Plugin loader class
class X_Editable_Plugin {
		
	public static $XE_VERSION = '1.4.6';
		
	public static $EDIT_CAP = 'edit_posts';
	
	public static $FIELDS = array();
	
	public static $JS = array();
	
	public static $ENQUEUED = false;
	
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
	
	
	/**
	*	Allows access to X_Editable_Plugin instance without duplication
	*
	*/
	public static function getInstance() {
		static $instance;
		$class = __CLASS__;	
		if ( ! $instance instanceof $class) {
			$instance = new $class;
		}
		return $instance;
	}
	
	// don't allow multiple instances
	private function __construct() {		
	
		self::$EDIT_CAP	= apply_filters('xe/edit_cap', self::$EDIT_CAP);
		
		require_once X_EDITABLE_PATH . 'fields/_base.php';
		require_once X_EDITABLE_PATH . 'inc/template-tag.php';
		
				 
		// XE_CAN_EDIT constant *must* be defined
		if ( current_user_can(self::$EDIT_CAP) ) {
		
			define('XE_CAN_EDIT', true);
		
			require_once X_EDITABLE_PATH . 'inc/field-functions.php';
			require_once X_EDITABLE_PATH . 'inc/ajax-callbacks.php';			
		}
		else
			define('XE_CAN_EDIT', false);
		
		
		// user-registered fields
		$this->user_fields = apply_filters('xe/user_fields', $this->user_fields);
		
		// Default user field path is 'fields' directory of theme
		$this->default_user_field_path = trailingslashit( apply_filters('xe/user_field_path', get_stylesheet_directory().'/fields/') );
		
			
		// Plugins can also set this manually
		if ( current_theme_supports('x-editable-acf') )
			define('X_EDITABLE_ACF_ENABLE', true);
		
		if ( defined('X_EDITABLE_ACF_ENABLE') && X_EDITABLE_ACF_ENABLE ) {

			require_once X_EDITABLE_PATH . 'fields/x-editable-acf-field.php';
			
			// Add admin page if user has edit cap
			if ( XE_CAN_EDIT && is_admin() )
				include_once X_EDITABLE_PATH . 'views/admin.php';
		}
		
		$this->register_scripts();
		$this->load_fields();
		
		add_action('xe/before_construct', array(&$this, '_xe_pre_field_setup'), 1, 4);
		add_action('xe/field_setup', array(&$this, '_xe_field_setup'));
	
	}

	
	private function register_scripts() {
		
		// Bootstrap editable css
		wp_register_style('x-editable', X_EDITABLE_URL . '/assets/bootstrap-editable.min.css', array('bootstrap'), false );
	}
	
	
	public function _xe_pre_field_setup($XE_Field, $meta_key, $object_id, $args){
		
		if ( ! defined('XE_CAN_EDIT') )
			exit;
		
		// Load scripts only if user can edit
		if ( XE_CAN_EDIT )
			$this->enqueue_scripts();
		
		// make output uneditable
		else
			$XE_Field->setHtml('tag', 'span');						
	}
	
	// Hook which "enqueues" the input's script(s).
	// HTML value, text, and CSS is set at this point
	public function _xe_field_setup($XE_Field){
		
		if ( $input = $XE_Field->options['input_type'] ) {
			// i forget what this does...
			do_action('xe/field_scripts/' . $input, self::$JS);
			
			$scripts = apply_filters( 'xe/register_field_scripts/type=' . $input, array() );
			if ( ! empty($scripts) ) {
				require_once X_EDITABLE_PATH . 'assets/xe-scripts.php';
				XE_Scripts::add($input, $scripts);
			}
			
			self::$JS[] = $input;
		}
	}
	
	// Prints the script tags in wp_footer
	public function _xe_print_scripts(){
		global $compress_scripts;
	
		$zip = $compress_scripts ? 1 : 0;
		if ( $zip && defined('ENFORCE_GZIP') && ENFORCE_GZIP )
			$zip = 'gzip';
		
		$q = implode(',', array_unique(self::$JS));
		$src = X_EDITABLE_URL . "/assets/x-editable-scripts.php?c={$zip}&load={$q}";
		$src = esc_attr($src);
		
		echo "<script>/* <![CDATA[ */\n";
		echo 'var xeditable = {"ajaxurl": "' . network_admin_url('admin-ajax.php') . '"};';
		echo "\n/* ]]> */</script>\n";
		
		echo "<script src=\"{$src}\"></script>\n";
		
	}
	
	public function enqueue_scripts() {
		
		if ( ! self::$ENQUEUED ) {
			
			add_action('wp_footer', array(&$this, '_xe_print_scripts'), 20);
			wp_enqueue_style('x-editable');
			
			self::$ENQUEUED = true;
		}

	}
	
	
	private function load_fields() {
		
		foreach($this->default_fields as $field) :
			
			$loc = X_EDITABLE_PATH . 'fields/' . $field . '.php';
			
			include_once $loc;
			
			self::$FIELDS[$field] = $loc;
			
		endforeach;
	
		foreach($this->user_fields as $field) :
			
			// default filename is {$fieldname}.php
			$default_location = $this->default_user_field_path . $field .'.php';
			
			$file_location = apply_filters('xe_' . $field . '_field_path', $default_location);
			
			if ( file_exists($file_location) ) {
				
				include_once $file_location;
				
				self::$FIELDS[$field] = $file_location;
			}
			
		endforeach;
	}
	
}

X_Editable_Plugin::getInstance();


function xe_register_script($input, $filepaths){	
	require_once X_EDITABLE_PATH . 'assets/xe-scripts.php';
	XE_Scripts::add($input, $filepaths);
	//X_Editable_Plugin::$JS[] = $input;
}

?>