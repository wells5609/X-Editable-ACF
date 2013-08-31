<?php

class X_Editable_ACF_Field extends X_Editable_Meta {
			
	function __construct($field_name, $post_id, $args = array()){
		
		$args['_acf'] = true;
		
		// Notice the argument order is different
		parent::__construct($post_id, $field_name, $args);
	}
	
	// Continues constructor
	public function acf_setup($id, $meta_key, $options){
		
		// object_name? prefix ID for rest of functions AFTER setting id (parent constructor)
		if ( isset($options['object']) && 'post' !== $options['object'] ) {
			$id = $options['object'] . '_' . $id;
			$this->addDataArg('object', $options['object']);
		}
		
		// key was given
		if ( strstr($meta_key, 'field_') ){
			$field = $meta_key;
			$this->addDataArg('key', $field);
		}
		else {	
			if ( function_exists('acf_field_key') )
				$field = acf_field_key($meta_key);
				
			if ( ! $field || ! function_exists('acf_field_key') )
				$field = get_field_reference($meta_key, $id);
		}
		
		$field_object = get_field_object( $field, $id );	
		
		$this->setHtml('label', $field_object['label']);
	
		$this->meta = $field_object; // this is important
		
		$this->set_input_type(); // only works w/o arg because overwritten below
				
	}
	
	
	// Adding API method - same as getMeta()
	public function fieldProp($name) {
		if ( isset($this->meta[$name]) )
			return $this->meta[$name];
	}
	
	
	/** is_single_value
	 *
	 *	@description: does this field only have 1 value? <- answers that question
	 *	@filters:
	 *		xe/field_types/multiple_values
	 *	
	**/
	
		public final function is_single_value() {
			
			$ft = $this->fieldProp('field_type');
			$val = $this->fieldProp('value');
			$multi = $this->fieldProp('multiple');
			
			// field-types where values are NOT single
			$multi_value_fields = array(
				'multi-select',
				'checkbox',
			);
			
			$multi_value_fields = apply_filters('xe/field_types/multiple_values', $multi_value_fields);
			
			if (isset($ft) && in_array( $ft, $multi_value_fields ) )
				return false;
				
			elseif (isset($val) && is_array($val) )
				return false;
			
			elseif (isset($multi) && ! $multi )
				return false;
			
			else
				return true;
		}
		
		
	/* ========================
		PRIVATE & PROTECTED 
	======================== */
	
	
	/**	set_value_and_text
	 *
	 *	@description: sets html['value'] and html['text'] vars
	 *
	 *	Fields can overwrite this function.
	 *	Alternatively, use filters (for less intensive changes)
	**/
		
		protected function set_value_and_text() {
			
			$type = $this->fieldProp('type');
			$value = $this->fieldProp('value');
			
			// set text defaults
			$text = $value;
			if ( empty($value) )
				$text = '<em>Empty</em>';

			// has return_format
			if ( $rtn_format = $this->fieldProp('return_format') ) {
				
				// value filter: xe/html/value/type={ACF-type}/format={ACF-return-format}
				$this->setHtml('value', 
					apply_filters('xe/html/value/type=' . $type . '/format=' . $rtn_format, $value)
				);				
				
				// text filter: xe/html/text/type={ACF-type}/format={ACF-return-format}
				$this->setHtml('text', 
					apply_filters('xe/html/text/type=' . $type . '/format=' . $rtn_format, $text)
				);
			}
			
			// no return format
			else {
				
				// value filter: xe/html/value/type={ACF-type}
				$this->setHtml('value', 
					apply_filters('xe/html/value/type=' . $type, $value)
				);
				
				// text filter: xe/html/text/type={ACF-type}
				$this->setHtml('text', 
					apply_filters('xe/html/text/type=' . $type, $text)
				);
			}
			
			return $this;
		}
	
	
	/** set_input_type
	 *
	 *	@description: Sets type of X-Editable input to use. Use as normal function or overwrite
	 *	@filters:
	 *		xe/input_type/type={{type}}
	 *
	**/
	
		public function set_input_type( $input_type = NULL ) {
			
			if ( NULL !== $input_type ) {
				$this->setOption('input_type', $input_type);	
				return $this;
			}
			
			$acf_type = $this->fieldProp('type');
			$input = 'text'; // default input type
			$namedFields = array(
				// ACF field-type name => X-Editable input name
				'text' => 'text',
				'textarea'	=> 'textarea',
				'select' => 'select',
				'date_picker' => 'date',
				'number' => 'number',
				'checkbox' => 'checklist',
			);
			
			if ( $namedFields[$acf_type] )
				$input = $namedFields[$acf_type];
			
			$this->setOption('input_type', 
				apply_filters('xe/input_type/type=' . $acf_type, 
					$input, 
					$this->meta
				) 
			);	
		
			return $this;
		}
		
}

?>