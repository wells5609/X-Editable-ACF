<?php

class XE_ACF_Field {
		
	public

		$object_id,				/*	(int) queried object ID	*/
			
		$field,					/** (array) ACF field object
								 *		type, field_type, value, etc.
								**/
		
		$html = array(),		/**	(array) HTML output:
								 *		tag			(string)	HTML tag to use (default 'a')
								 *		text		(string)	text to display
								 *		value		(string)	value for data-value
								 *		css_class	(array)		CSS classes for element
								 *		data_args	(array)		data-* attributes
								**/
								
		$options = array();		/** (array) options:
								 * 		show_label		(boolean)	show a label before the element
								 * 		show_external	(boolean)	show the field's values externally (ie outside the editable element)
								 *		input_type		(string)	input type to use
								**/
				
	/** __construct
	 *
	 *	SETS:
	 *		object_id
	 *		field
	 *		html defaults
	 *		...stuff
	 *	
	**/
	
		function __construct( $field_name, $object_id, $object_name = false ) {
			
			$this->object_id = (int) $object_id;
			
			$this->set_html('tag', 'a');
			$this->set_html('css_class', array());
			$this->set_html('data_args', array());
			
			
			// object_name? prefix ID for rest of functions AFTER setting var
			if ( $object_name ) {
				$object_id = $object_name . '_' . $object_id;
				$this->add_data_arg('object_name', $object_name);
			}
			
			$this->set_option('show_label', true);
			$this->set_option('show_external', false);
			$this->set_option('restrict_choices', false);
									
			// user defined get_field_key function
			if ( function_exists('get_field_key') ) {
				$field = get_field_key($field_name);			
			}
			// field key not found |OR| user has not defined get_field_key function
			if ( ! $field || ! function_exists('get_field_key') ) {
				$field = get_field_reference($field_name, $object_id);
			}
			// neither method found field key
			if ( ! $field ) {
				exit('Could not find field reference for ' . $field_name . '. Define and return via get_field_key() function.');	
			}
			
			$this->field = get_field_object( $field, $object_id );
			
			$this->set_html('label', $this->field['label']);
			
		}

		
	/* ============
		MAGIC
	============ */
	
			
		/** __get
		 *
		 *	@description: Gets a variable value.
		 *
		**/
			function __get($variable) {
				return $this->$variable;
			}
		
		
		/** __set
		 *	
		 *	@description: Sets a variable value.
		 *
		**/
			function __set($variable, $value) {
				$this->$variable = $value;
			}
			
		/** __isset
		 *
		 *	@description: checks if a variable is set, returns true or false.
		 *
		**/
			function __isset($variable) {
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
		
		/** get_{{variable}}
		 *	
		 *	@description: Return the value of array variable given a key.
		 *
		**/
			function get_field($key) {
				return $this->field[$key];
			}
			function get_html($key) {
				return $this->html[$key];
			}
			function get_option($key) {
				return $this->options[$key];
			}
			function get_data_arg($key) {
				return $this->html['data_args'][$key];
			}
				
			
		/** set_html
		 *
		 *	@description: sets $html[$name] to $value to be used in X-Editable element
		 *
		**/
			function set_html( $name, $value ) {
				$this->html[$name] = $value;
			}
		
		
		/** set_option
		 *
		 *	@description: sets $options[$name] to $value to be used in X-Editable element
		 *
		**/
			function set_option( $name, $value ) {
				$this->options[$name] = $value;
			}
	
		
		
		/** add_data_arg
		 *
		 *	@description: adds a data-* attribute via $data_args, like: data-{{$name}}="{{$value}}"
		 *
		**/
			function add_data_arg( $name, $value = NULL ) {
				if ( is_array($name) ) {
					foreach($name as $k => $v) :
						$this->html['data_args'][$k] = $v;
					endforeach;
				}
				else {
					$this->html['data_args'][$name] = $value;
				}
			}
		
		
		/** add_css_class
		 *
		 *	@description: adds a css class if string, or several if an array
		 *
		**/
			function add_css_class( $class ) {
				if ( is_array($class) ) {
					foreach($class as $c) :
						$this->html['css_class'][] = $c;
					endforeach;
				}
				else {
					$this->html['css_class'][] = $class;
				}
			}
			
	
	/* ============
		BOOLEANS
	============ */
	
		
		/** is_single_value
		 *
		 *	@description: does this field only have 1 value? <- answers that question
		 *	@filters:
		 *		xe/field_types/multiple_values
		 *	
		**/
		
			function is_single_value() {
				
				// field-types where values are NOT single
				$multi_value_fields = array(
					'multi-select',
					'checkbox',
				);
				
				$multi_value_fields = apply_filters('xe/field_types/multiple_values', $multi_value_fields);
				
				if ( isset($this->field['field_type']) && in_array( $this->field['field_type'], $multi_value_fields ) ) {
					return false;
				}
				
				elseif ( isset($this->field['value']) && is_array($this->field['value']) ) {
					return false;
				}
				elseif ( isset($this->field['multiple']) && ( (true||1) != $this->field['multiple'] ) ) {
					return false;
				}
				
				else {
					return true;	
				}
				
			}
		
	
	/* ================
		SHOW-ERS
	================ */
	
	
		/** show_label
		 *
		 *	@description: Shows a label.
		 *
		**/
			function show_label() {
				
				do_action('xe/create_label', &$this->field);
					
			}
		
		
		/** show_values
		 *
		 *	@description: Shows values externally.
		 *
		**/ 
			function show_values( $as_ul = false ) {
				
				do_action_ref_array('xe/create_external', array( &$this->field, &$this->html, $as_ul));
					
			}	
	
			
		/**	html
		 *
		 *	@description: The element HTML output
		 *
		**/
			
			function html() {
				
				$this->set_value_and_text();
				
				if ( empty($this->options['input_type'])) {
					$this->set_input_type();
				}
				
				$this->setup_source();
				$this->setup_css_class();
				
				do_action('xe/create_element', $this->field, $this->object_id, $this->html, $this->options);
			
			}
	

	/* ========================
		PRIVATE & PROTECTED 
	======================== */
	
	
	/**	set_value_and_output
	 *
	 *	@description: sets html_value and html_display vars
	 *
	 *	Most fields should overwrite this function
	 *
	**/
		
		protected function set_value_and_text() {
			
			// Has return_format
			if ( $this->field['return_format'] ) {
				
				// Value
				
				$this->set_html('value', apply_filters('xe/html/value/type=' . $this->field['type'] . '/format=' . $this->field['return_format'], $this->field['value']));				
				
				// Text
				
				if ( empty($this->field['value']) ) {
					$this->set_html('text', apply_filters('xe/html/text/type=' . $this->field['type'] . '/format=' . $this->field['return_format'], '<em>Empty</em>'));
				}
				else {
					$this->set_html('text', apply_filters('xe/html/text/type=' . $this->field['type'] . '/format=' . $this->field['return_format'], $this->field['value']));				
				}
				
			}
			
			// no return format
			else {
				
				// Value
				
				$this->set_html('value', apply_filters('xe/html/value/type=' . $this->field['type'], $this->field['value']));
				
				// Text
				
				if ( empty($this->field['value']) ) {
					$this->set_html('text', apply_filters('xe/html/text/type=' . $this->field['type'], '<em>Empty</em>'));
				}
				else {
					$this->set_html('text', apply_filters('xe/html/text/type=' . $this->field['type'], $this->field['value']));				
				}
				
			}
					
		}
	
	
	/** setup_input_type
	 *
	 *	@description: Sets type of X-Editable input to use. Use as normal function or overwrite
	 *	@filters:
	 *		xe/input_type/type={{type}}
	 *
	**/
		protected function set_input_type( $input_type = NULL ) {
			
			if ( NULL !== $input_type ) {
				$this->set_option('input_type', $input_type);	
			}
			else {
				
				$acf_type = $this->field['type'];
				
				$namedFields = array(
					// ACF TYPE => X-EDITABLE INPUT
					'text' => 'text',
					'textarea'	=> 'textarea',
					'select' => 'select',
					'date_picker' => 'date',
					'number' => 'number',
				);
				
				// if acf_type is found in the array above (as key)
				if ( $namedFields[$acf_type] ) {
					// set input type to value
					$this->set_option('input_type', $namedFields[$acf_type] );
				}
				
				// otherwise filter - default 'text'
				else {
					$this->set_option('input_type', apply_filters('xe/input_type/type=' . $this->field['type'], 'text', &$this->field) );
				}
				
			}
		
		}
		
	
	/**	_source
	 *
	 *	@description: Sets html['source'] for fields with 'choices'
	 *
	**/
		private function setup_source() {
			
			$choices = $this->field['choices'];
			
			if ( isset($choices) ) {
				$this->set_html('source', $this->to_json($choices));
			}
									
		}
		
	
	/** _css_class
	 *
	 *	@description: adds default & some dependent CSS classes to the element
	 *
	**/
		private function setup_css_class() {
			
			$type = $this->field['type'];
			$field_type = $this->field['field_type'];
						
			$this->add_css_class('x-editable-element');
			$this->add_css_class('acf-type-' . $type); 	
						
			if ( isset($field_type) ) {
				$this->add_css_class('acf-field-type-' . $field_type); 	
			}
			
			if ( $this->is_single_value() ) {
				$this->add_css_class('single-value');	
			}
			
			if ( $this->options['show_external'] ) {
				$this->add_css_class('values-external');	
			}
			
			$this->set_html('css_class', apply_filters('xe/html/css_class/type=' . $type, $this->html['css_class'], &$this->field));
			
		}
	
	
	/** to_json
	 *
	 *	@description: json_encode a variable as array string or objects (default)
	 *
	**/
	
	function to_json( $thing, $as_string = false ) {
		
		// Don't restrict user input to existing choices
		if ( $as_string || ! $this->options['restrict_choices'] ) {
			$json = json_encode($thing);
		}
		// Restrict user input to existing choices
		else {
			$json = json_encode($thing, JSON_FORCE_OBJECT);	
		}
		
		return $json;
		
	}

	
}

?>