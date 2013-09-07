<?php

// Plugin loader class
class X_Editable_Plugin {
		
	public static $XE_VERSION = '1.4.6';
		
	public static $EDIT_CAP = 'edit_posts';
	
	public static $FIELDS = array();
	
	public static $JS = array();
	
	public static $IS_ENQUEUED = false;
	
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
	
	
	//	Access the X_Editable_Plugin instance without duplication
	public static function getInstance() {
		static $instance;
		$class = __CLASS__;	
		if ( ! $instance instanceof $class) {
			$instance = new $class;
		}
		return $instance;
	}
	
	private function __construct() {		
		
		$this->_setDefaults();
		
		// Load X_Editable_Meta class and template tag functions
		require_once X_EDITABLE_PATH . 'fields/_base.php';
		require_once X_EDITABLE_PATH . 'inc/template-tag.php';
		
				
		// XE_CAN_EDIT constant *must* be defined
		if ( current_user_can(self::$EDIT_CAP) ) {
		
			define('XE_CAN_EDIT', true);
		
			require_once X_EDITABLE_PATH . 'inc/field-functions.php';
			require_once X_EDITABLE_PATH . 'inc/ajax-callbacks.php';			
		}
		else define('XE_CAN_EDIT', false);
		
		
		// Plugins can also set this manually
		if ( current_theme_supports('x-editable-acf') )
			define('X_EDITABLE_ACF_ENABLE', true);
		
		if ( defined('X_EDITABLE_ACF_ENABLE') && X_EDITABLE_ACF_ENABLE ) {
			
			require_once X_EDITABLE_PATH . 'fields/x-editable-acf-field.php';
			
			if ( XE_CAN_EDIT && is_admin() )
				include_once X_EDITABLE_PATH . 'views/admin.php';
		}
		
		// Autoload X_Editable_ACF_* field classes
		spl_autoload_register(array(&$this, 'autoload'));
		
		$this->_registerScriptsStyles();
		$this->_loadFields();
		
		add_action('xe/before_construct', array(&$this, '_xe_pre_field_setup'), 1, 4);
		add_action('xe/field_setup', array(&$this, '_xe_field_setup'));
		
	}
	
	private function _setDefaults(){
		
		// Set plugin-wide default edit capability
		self::$EDIT_CAP		=	apply_filters('xe/edit_cap', self::$EDIT_CAP);
		
		// Get user-registered fields
		$this->user_fields	=	apply_filters('xe/user_fields', $this->user_fields);
		
		// Default user field path is 'fields' directory of theme
		$this->default_user_field_path	=	trailingslashit( apply_filters('xe/user_field_path', get_stylesheet_directory().'/fields/') );		
	}
	
	
	/**
	* X_Editable_ACF_* class autoloader
	*
	* Only works with included default fields
	*/
	public function autoload($class){
		// only for X_Editable_ACF_* classes
		if ( strpos($class, 'X_Editable_ACF') !== 0 )
			return;
		
		// default path for autoloaded classes
		$dir = apply_filters('xe/autoload_path', X_EDITABLE_PATH . 'fields', $class);
		$class = strtolower(str_replace('X_Editable_ACF_', '', $class));
		$filepath = $dir . '/' . $class . '.php';
		
		// find filepath from registered fields array
		if ( in_array($class, array_keys(self::$FIELDS)) )
			$filepath = self::$FIELDS[$class];
		
		if ( @file_exists($filepath) )
			require_once $filepath;
	}


	/** action: xe/before_construct
	* 
	* Runs checks for fields to determine if scripts should be loaded
	*/
	public function _xe_pre_field_setup($XE_Field, $meta_key, $object_id, $args){
		
		if ( !defined('XE_CAN_EDIT') ) 
			exit;
		
		// Load scripts only if user can edit
		if ( XE_CAN_EDIT ) 
			$this->enqueue_scripts();
		
		// otherwise make output uneditable
		else 
			$XE_Field->setHtml('tag', 'span');						
	}

	
	/** action: xe/field_setup
	*
	* Hook which "enqueues" the input's script(s).
	* HTML value, text, and CSS is set at this point
	* 
	* NOTE: This does not (yet) work with user-defined (non-core) fields.
	*/
	public function _xe_field_setup($XE_Field){
		
		if ( $input = $XE_Field->options['input_type'] ) {
			// forget what this does...
			do_action('xe/field_scripts/' . $input, self::$JS);
			
			$scripts = apply_filters( 'xe/register_field_scripts/type=' . $input, array() );
			if ( ! empty($scripts) ) {
				require_once X_EDITABLE_PATH . 'assets/xe-scripts.php';
				XE_Scripts::add($input, $scripts);
			}
			
			self::$JS[] = $input;
		}
	}

	
	/** action: wp_footer
	* 
	* Prints the scripts in wp_footer
	*
	*/
	public function _xe_print_scripts(){
		global $compress_scripts;
	
		$zip = $compress_scripts ? 1 : 0;
		if ( $zip && defined('ENFORCE_GZIP') && ENFORCE_GZIP )
			$zip = 'gzip';
		
		$q = implode(',', array_unique(self::$JS));
		$src = esc_attr(X_EDITABLE_URL . "/assets/x-editable-scripts.php?c={$zip}&load={$q}");
		
		echo 
			"<script>/* <![CDATA[ */\n",
			'var xeditable = {"ajaxurl": "' . network_admin_url('admin-ajax.php') . '"};',
			"\n/* ]]> */</script>\n",
			
			"<script src=\"{$src}\"></script>\n";
	}
	
	/** enqueue_scripts
	*
	* Called in _xe_pre_field_setup
	*
	*/
	public function enqueue_scripts() {
		if ( ! self::$IS_ENQUEUED ) {
			
			add_action('wp_footer', array(&$this, '_xe_print_scripts'), 20);
			wp_enqueue_style('x-editable');
			
			self::$IS_ENQUEUED = true;
		}
	}
	

	/** -------- PRIVATES -------- */
	
	
	// Registers the x-editable bootstrap CSS
	private function _registerScriptsStyles() {
		
		wp_register_style('x-editable', X_EDITABLE_URL . '/assets/bootstrap-editable.min.css', array('bootstrap'), false );
	}
	
	
	// Loads default and user fields (with file paths) into plugin class var $FIELDS array (static)
	private function _loadFields() {
		
		foreach($this->default_fields as $field){
			
			$loc = X_EDITABLE_PATH . 'fields/' . $field . '.php';
			self::$FIELDS[$field] = $loc;
		}
		foreach($this->user_fields as $field){
		
			// default fields location is theme directory with filename {$fieldname}.php
			$default_location = $this->default_user_field_path . $field .'.php';
			$file_location = apply_filters('xe_' . $field . '_field_path', $default_location);
			
			if ( file_exists($file_location) )
				self::$FIELDS[$field] = $file_location;
		}
	}
	
	
	final public function __clone() {
		trigger_error( "Singleton. No cloning allowed!", E_USER_ERROR );
	}
	final public function __wakeup() {
		trigger_error( "Singleton. No serialization allowed!", E_USER_ERROR );
	}

}

X_Editable_Plugin::getInstance();

/**
function xe_register_script($input, $filepaths){	
	require_once X_EDITABLE_PATH . 'assets/xe-scripts.php';
	XE_Scripts::add($input, $filepaths);
	//X_Editable_Plugin::$JS[] = $input;
}
*/

?>