<?php

include_once 'fields/taxonomy.field.php';
include_once 'fields/textarea.field.php';
include_once 'fields/date.field.php';
include_once 'fields/number.field.php';

class XE_ACF_Field {
		
	public

		$object_id,		// (int) queried object ID
		
		$field,			// (array) ACF field
		
		$html,			// (array) HTML output
		
		$data_args,		// (array) "data-*" HTML attributes
		
		$valid_input_types, // (array)
		
		$options;		// (array) options:
						// 	show_label		(boolean)	show a label
						// 	show_external	(boolean)	show the field's values elsewhere
						//	input_type		(string)	input type to use
		
				
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
			
			$this->data_args = array();
			
			$this->options = array();
			$this->options['show_label'] = true;
			$this->options['show_external'] = false;
			
			if ( $object_name ) {
				
				$object_id = $object_name . '_' . $object_id;
								
				$this->add_data_arg('object_name', $object_name);
			
			}
						
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
			
			$this->html = array();
			$this->html['tag'] = 'a';
			$this->html['css_class'] = array();
			$this->html['label'] = $this->field['label'];
			
			// validate field and field_type - is this really necessary??
			//$this->validate();
			
		}
	
	
	/** add_data_arg
	 *
	 *	@description: adds a data-* attribute via $data_args, like: data-{{$name}}="{{$value}}"
	 *
	**/
		function add_data_arg( $name, $value = NULL ) {
			
			if ( is_array($name) ) {
				$data = $name;
			}
			else {
				$data = array( $name => $value );
			}
			
			$this->data_args = array_merge($this->data_args, $data);
			
		}
		
		/** add_data_args
		 *
		 *	@description: adds data-* attributes from an array of 'name' => 'value' pairs
		 *	@uses: add_data_arg
		 *
		**/
			function add_data_args($args) { 
				$this->add_data_arg( $args, NULL ); 
			}
		
	
	/** set_html
	 *
	 *	@description: sets $html[$attribute] to $value to be used in X-Editable element
	 *
	**/
		function set_html( $attribute, $value ) {
			$this->html[$attribute] = $value;
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
			elseif ( is_string($class) ) {
				$this->html['css_class'][] = $class;
			}
		}
		
	
	/** is_single_value
	 *
	 *	@description: does this field only have 1 value?
	 *	@filters:
	 *		xe/multiple_value_field_types
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
	
	
	/** set_label
	 *
	 * @description: Defines label HTML.
	 *
	**/
		function show_label() {
			
			do_action('xe/create_label', &$this->field);
				
		}
	
	/** set_external
	 *
	 * @description: Defines external (values) HTML.
	 *
	**/ 
		function show_values( $as_ul = false ) {
			
			do_action_ref_array('xe/create_external', array( &$this->field, &$this->html, $as_ul));
				
		}	
	
		
	/**
	 *	@description: The element HTML output
	 *	@actions:
	 *		xe/before_element	
	 *		xe/after_element
	**/
		
		function html() {
			
			$this->setup_html();
			
			do_action('xe/create_element', $this->field, $this->object_id, $this->html, $this->data_args, $this->options);
					
		}
	

	/* ========================
		PRIVATE & PROTECTED 
	======================== */
	
	
	/**	setup_html
	 *
	 *	@description: Sets up HTML for element: CSS classes, values/output, source.
	 *		Adds actions to print label/external if set	
	**/
	
		function setup_html() {
			
			$this->set_value_and_text();
			
			$this->set_input_type();
			
			$this->set_source();
			
			$this->set_css_class();
			
		}
		
	
	/**	set_value_and_output
	 *
	 *	@description: sets html_value and html_display vars and applies filters
	 *	@filters:
	 *		xe/html/value
	 *		xe/html/value/type={{acf-type}}
	 * 		xe/html/text
	 *		xe/html/text/type={{acf-type}}
	 *
	**/
		
		protected function set_value_and_text() {
			
			// Value
			$this->html['value'] = $this->field['value'];
			
			// Text
			if ( empty($this->field['value']) ) {
				$this->html['text'] = 'Empty';
			}
			else {
				$this->html['text'] = $this->field['value'];
			}
			
		}
	
	
	/** determine_input_type
	 *
	 *	@description: Sets type of X-Editable input to use. Use as normal function or overwrite
	 *	@filters:
	 *		xe/input_type
	 *	
	 *	TO-DO: refactor - not sure if namedFields key search is working...
	 *
	**/
		protected function set_input_type( $input_type = NULL ) {
			
			if ( NULL !== $input_type ) {
				$this->options['input_type'] = $input_type;
			}
			else {
				
				$namedFields = array(
					'text' => 'text',
					'textarea'	=> 'textarea',
					'select' => 'select',
					'date' => 'date_picker', // just to see if these works...
				);
				// if 'type' is found in the array above (as a value)
				if ( $inputType = array_search($this->field['type'], $namedFields) ) {
					$this->options['input_type'] = $inputType;
				}
				// otherwise expecting filter
				else {
					$this->options['input_type'] = apply_filters('xe/input_type/type=' . $this->field['type'], 'text', &$this->field);
				}
			}
			
		}
		
	
	/**	set_source
	 *
	 *	@description: Sets html['source'] for selects, checklists, etc.
	 *	@filters:	
	 *		xe/html/source/type={{acf-field-type<}}
	 *
	 *	NOTE: Uses field->choices if exists
	 *
	 *	TO-DO: Check if works (probably need json_encode somewhere)	
	**/
	
		protected function set_source( $source = NULL ) {
			
			if ( NULL !== $source ) {
				$this->html['source'] = $source;
			}
			
			elseif ( isset($this->field['choices']) ) {
				$this->html['source'] = $this->field['choices'];
			}
						
		}
	
	
	/** set_css_class
	 *
	 *	@filters:
	 *		xe/html/css_class/type={{acf-type}}
	 *
	**/
		private function set_css_class() {
			
			$this->html['css_class'] = array();
			
			$this->html['css_class'][] = 'x-editable-element';
			$this->html['css_class'][] = 'acf-type-' . $this->field['type']; 	
			
			if ( isset($this->field['field_type']) ) {
				$this->html['css_class'][] = 'acf-field-type-' . $this->field['field_type']; 	
			}
			
			if ( $this->is_single_value() ) {
				$this->html['css_class'][] = 'single-value';	
			}
			
			if ( $this->options['show_external'] ) {
				$this->html['css_class'][] = 'values-external';	
			}
			
			$this->html['css_class'] = apply_filters('xe/html/css_class/type=' . $this->field['type'], &$this->html['css_class'], &$this->field );
			
		}
	
	
}


?>