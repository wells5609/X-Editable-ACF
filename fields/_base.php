<?php

class X_Editable_Meta {
		
	public
	
	/**	(int) queried object ID	**/
		$id,

	/**	(array) meta:
	 *	single		(boolean)	whether value is single
	 *	value		(mixed)		meta_value from db
	 *	...
	**/
		$meta = array(
			'single' => false,
			//'name' => ...
		),		
		
	/**	(array) html
	 *	tag			(string)	HTML tag to use (default 'a')
	 *	text		(string)	text to display
	 *	value		(string)	value for data-value
	 *	css_class	(array)i	CSS classes for element
	 *	data_args	(array)a	data-* attributes
	**/
		$html = array(
			'tag'	=> 'a',
			'css_class'	=> array(),
			'data_args'	=> array(),
		),
								
	/** (array) options:
	 * 	show_label		(boolean)	show a label before the element
	 * 	show_external	(boolean)	show the field's values externally (ie outside the editable element)
	 *	input_type		(string)	input type to use
	 *	object			(string)	object to edit (post, user, term)
	 *		...
	**/	
		$options = array(
			'show_label' => true,
			'show_external' => false,
			'input_type' => 'text',
			'object' => 'post',
		);
	
	
	/**	(boolean) _is_setup
	 *	Whether the output variables are setup yet
	**/
		private $_is_setup = false;
	
	
	/** __construct
	 *	
	**/
	
		public function __construct( $id, $meta_key, $args = array() ) {
						
			if ( ! defined('XE_CAN_EDIT') )
				exit;
			// Load scripts only if user can edit
			if ( XE_CAN_EDIT ) {
				XE_ACF_Plugin::enqueue_scripts();
			}
			else {
				$this->setHtml('tag', 'span');
			}
		
			// set vars
			$this->id = (int) $id;
			
			
			// args passed?
			if ( $args['object'] ) {
				$this->setOption('object', $args['object']);	
			}
			
			if ( $args['single'] ) {
				$this->setMeta('single', $args['single']);
				unset($args['single']); // unset before options merge (see below)
			}
		
			// Options - add unknown args
			$options = array_merge($this->options, $args);
			$this->options = apply_filters('xeditable/construct/options', array_merge($this->options, $args), $meta_key);
			
			// HTML
			// $this->html = apply_filters('xeditable/construct/html', $this->html, $this->getMeta('key'), $this->options);
			
			// Value
			$meta_was_returned = apply_filters('xeditable/construct/meta/meta_key=' . $meta_key, false, $id, $meta_key, $this->options);
			
			if ( $meta_was_returned && ! empty($meta_was_returned) ) {
				$this->meta = $meta_was_returned;
				
			}
			else {
			
				$this->setMeta('key', $meta_key);
				$this->setMeta('name', $meta_key);
				
				if ( 'post' === $this->getOption('object') ) {
				
					$this->setMeta('value', get_post_meta($id, $this->getMeta('key'), $this->getMeta('single')));
				}
				elseif ( 'user' === $this->getOption('object') ) {
					
					$this->setMeta('value', get_user_meta($id, $this->getMeta('key'), $this->getMeta('single')));
				}
				// Take first element from array value if single
				if ( $this->getMeta('single') && is_array($this->getMeta('value')) ) {
					
					$this->setMeta('value', $this->meta['value'][0]);
				}
				if ( ! $this->hasOption('label') && ! $this->hasMeta('label') ) {
				
					$this->setMeta('label', ucwords( implode(' ', explode( '_', str_replace('-', ' ', $meta_key) )) ));
				}
				
			}
			
		}
		
		
	/* ============
		MAGIC
	============ */
	
			
		/** __get
		 *
		 *	@description: Gets a variable value.
		 *
		**/
			public function __get($variable) {
				return $this->$variable;
			}
		
		
		/** __set
		 *	
		 *	@description: Sets a variable value.
		 *
		**/
			public function __set($variable, $value) {
				$this->$variable = $value;
			}
			
		/** __isset
		 *
		 *	@description: checks if a variable is set, returns true or false.
		 *
		**/
			public function __isset($variable) {
				if ( isset($this->$variable) && ! empty($this->$variable) ) {
					return true;
				}
				else {
					return false;	
				}
			}
						
				
	/* =============
		"API"
	============= */
	
		/**
		 *	META, HTML, OPTION
		 *	
		 *	Functions available:
		 *		get_* 
		 *		set_*
		 *		has_*
		**/
				
			/**
			 *	META
			**/
			
				public function getMeta($key) {
					return $this->meta[$key];
				}
				public function setMeta( $name, $value ) {
					$this->meta[$name] = $value;
				}
				public function hasMeta($name) {				
					if ( isset($this->meta[$name]) && ! empty($this->meta[$name]) )
						return true;	
					else
						return false;
				}
		
						
			/** 
			 *	HTML
			**/
		
				public function getHtml($key) {
					return $this->html[$key];
				}
				public function setHtml( $name, $value ) {
					$this->html[$name] = $value;
				}
				public function hasHtml($name) {				
					if ( isset($this->html[$name]) && ! empty($this->html[$name]) )
						return true;	
					else
						return false;
				}
			
			
			/** 
			 *	OPTION
			**/
			
				public function getOption($key) {
					return $this->options[$key];
				}
				
				public function setOption( $name, $value ) {
					$this->options[$name] = $value;
				}
				
				public function hasOption($name) {				
					if ( isset($this->options[$name]) && ! empty($this->options[$name]) )
						return true;	
					else
						return false;
				}
		
		/**
		 *	DATA ARG, CSS CLASS
		 *	
		 *	Functions available:
		 *		get_* 
		 *		add_*
		 *		has_*
		**/	
			
			/** 
			 *	DATA ARGS
			**/
	
				public function getDataArg($key) {
					return $this->html['data_args'][$key];
				}
			
				public function addDataArg( $name, $value = NULL ) {
					if ( is_array($name) ) {
						foreach($name as $k => $v) :
							$this->html['data_args'][$k] = $v;
						endforeach;
					}
					else {
						$this->html['data_args'][$name] = $value;
					}
				}
				
				public function hasDataArg($name){
					if ( isset($this->html['data_args'][$name]) && ! empty($this->html['data_args'][$name]) )
						return true;	
					else
						return false;
				}
			
			
			/** 
			 *	CSS CLASS
			**/
			
				public function getCssClass($name) {
					return $this->html['css_class'][$name];
				}
			
				public function addCssClass($name) {
					if ( is_array($name) ) {
						foreach($name as $c) :
							$this->html['css_class'][] = $c;
						endforeach;
					}
					else {
						$this->html['css_class'][] = $name;
					}
				}
				
				public function hasCssClass($name){
					if ( isset($this->html['css_class'][$name]) )
						return true;	
					else
						return false;
				}
			
		
		/** is_single_value
		 *
		 *	@description: does this field only have 1 value? <- answers that question
		 *	@filters:
		 *		xeditable/is_single_value/meta_key={meta_key}
		 *	
		**/
		
			public function is_single_value() {
				
				if ( ! $this->getMeta('single') ) {
					$value = false;	
				}
				
				return apply_filters('xeditable/is_single_value', $value, $this->meta, $this->options);	
												
			}
		
	
	/* ================
		SHOW-ERS
	================ */
	
	
		/** show_label
		 *
		 *	@description: Shows a label.
		 *
		**/
			public function showLabel() {
				
				if ( ! $this->_is_setup ) {
					$this->setup();	
				}
				
				do_action('xe/create_label', $this->meta);
					
			}
		
		
		/** show_values
		 *
		 *	@description: Shows values externally.
		 *
		**/ 
			public function showValues( $as_ul = false ) {
				
				if ( ! $this->_is_setup ) {
					$this->setup();	
				}
				
				do_action_ref_array('xe/create_external', array($this->id, $this->meta, $this->html, $as_ul));
					
			}	
	
			
		/**	html
		 *
		 *	@description: The element HTML output
		 *
		**/
			
			public function html() {
				
				if ( ! $this->_is_setup ) {
					$this->setup();	
				}
				
				do_action('xe/create_element', $this->meta, $this->id, $this->html, $this->options);
			
			}
	

	/* ========================
		PRIVATE & PROTECTED 
	======================== */
	
	private function setup() {
		
		$this->set_value_and_text();
		$this->setupCssClass();
		$this->setupSource();
		
		$this->_is_setup = true;	
		
	}
	
	
	/**	set_value_and_output
	 *
	 *	@description: sets html['value'] and html['text'] vars
	 *
	 *	Most fields should overwrite this function
	 *
	**/
		
		protected function set_value_and_text() {
			
			// Value
			
			$value = $this->getMeta('value');
			
			$this->setHtml('value', apply_filters('xeditable/html/value', $value, $this->meta, $this->options));				
			
			
			// Text
			
			if ( empty($value) ) {
			
				$this->setHtml('text', apply_filters('xeditable/html/text/empty', 'Empty', $this->meta, $this->options));
			}
			
			elseif ( $this->getOption('edit_link') ) {
			
				$this->setHtml('text', apply_filters('xeditable/html/text/edit_link', 'Edit', $this->meta, $this->options));
			}
			
			else {
			
				$this->setHtml('text', apply_filters('xeditable/html/text', $value, $this->meta, $this->options));
			}
				
		}
	
	

	/** setup_input_type
	 *
	 *	@description: Sets type of X-Editable input to use. Use as normal function or overwrite
	 *	@filters:
	 *		xe/input_type/type={{type}}
	 *
	**/
		public function set_input_type( $input_type ) {
			
			$this->setOption('input_type', apply_filters('xeditable/input_type', $input_type, $this->meta, $this->options) );
			
		}
	
	
	/** _css_class
	 *
	 *	@description: adds default & some dependent CSS classes to the element
	 *
	**/
		private function setupCssClass() {
			
			$input_type = $this->getOption('input_type');
						
			$this->addCssClass('x-editable-element');
						
			if ( isset($input_type) ) {
				$this->addCssClass('input-type-' . $input_type); 	
			}
			
			if ( $this->is_single_value() ) {
				$this->addCssClass('single-value');	
			}
			
			if ( $this->getOption('show_external') ) {
				$this->addCssClass('values-external');	
			}
			
			if ( $this->getOption('edit_link') || 'edit' === strtolower($this->getHtml('text')) ) {
				$this->addCssClass('edit-link');
			}
			
			$this->html['css_class'] = apply_filters('xeditable/html/css_class', $this->html['css_class'], $this->meta, $this->options);
			
		}
	
		
	/**	_source
	 *
	 *	@description: Sets html['source'] for fields with 'choices'
	 *
	**/
		private function setupSource() {
			
			$choices = $this->meta['choices'];
			
			if ( isset($choices) ) {
				$this->setHtml('source', $this->to_json($choices));
			}
									
		}
		
	
	/** to_json
	 *
	 *	@description: json_encode a variable as array string or objects (default)
	 *
	**/
	
	public function to_json( $thing, $as_string = false ) {
		
		// Don't restrict user input to existing choices
		if ( $as_string || ! $this->getOption('restrict_choices') ) {
			$json = json_encode($thing);
		}
		// Restrict user input to existing choices
		else {
			$json = json_encode($thing, JSON_FORCE_OBJECT);	
		}
		
		return $json;
		
	}

	
}

class X_Editable_ACF_Field extends X_Editable_Meta {
	
	
	function getFieldProp($name) {
		return $this->getMeta($name);
	}
	
	
	function __construct($post_id, $field_name, $args = array()){
		
		add_filter('xeditable/construct/meta/meta_key=' . $field_name, array($this, 'field_construct'), 5, 4);
		
		parent::__construct($post_id, $field_name, $args);	
		
	}
	
	function field_construct($val, $id, $meta_key, $options){
		
		// object_name? prefix ID for rest of functions AFTER setting id (parent constructor)
		if ( $options['object'] ) {
			$id = $options['object'] . '_' . $id;
			$this->addDataArg('object', $options['object']);
		}
							
		// user defined get_field_key function
		if ( function_exists('get_field_key') ) {
		
			$field = get_field_key($meta_key);
		}
		
		// field key not found |OR| user has not defined get_field_key function
		if ( ! $field || ! function_exists('get_field_key') ) {
		
			$field = get_field_reference($meta_key, $id);
		}
		
		// found field key - checks to make sure value isn't "field_{field_name}"
		if ( $field && ! strstr($field, 'field_' . $meta_key) ) {
			
			$field_object = get_field_object( $field, $id );	
			
			$this->setHtml('label', $field_object['label']);
		
			return $field_object;
			
		}
		
	}
		
	
}



?>