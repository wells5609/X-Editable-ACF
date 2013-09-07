<?php
/**
* @package X-Editable-ACF
*/

class X_Editable_ACF_True_False extends X_Editable_ACF_Field {
	
	var $valid_inputs = array(
		'select',
		'checklist',
	);
	
	function __construct( $field_name, $object_id, $args = array() ) {
		
		// don't remove
		parent::__construct($field_name, $object_id,  $args = array());
			
		/* Field-specific args */
		
		/* Filters */
		add_filter('xe/external/text/type='. $this->fieldProp('type'), array($this, 'external_text'), 10, 2);
	
	}
	
	
	// filter applied in X_Editable_ACF_Functions::create_external()
	function external_text( $field_value, $field ) {
		if ( empty($field_value) )
			return 'Empty';
		elseif ( 1 == $field_value )
			return 'Yes';
		elseif ( 0 == $field_value )
			return 'No';
	}
		
	
	/** =======================
		Redefined functions 
		(from parent class)
	======================== */
	
	function setSource() {
		
		$source = array(
			1 => 'Yes',
			0 => 'No',
		);
		
		$this->setHtml('source', json_encode($source, JSON_FORCE_OBJECT));
	}
		
	
	function set_input_type($input_type = NULL) {
		
		if ( ! is_null($input_type) && in_array($input_type, $this->valid_inputs) )
			$input = $input_type;	
		
		else
			$input = $this->valid_inputs[0]; // select
		
		$this->setOption('input_type', $input);
			
	}
	
	
	// sets value and text for X-Editable element attributes
	function set_value_and_text() {
		
		$value = $this->fieldProp('value');
		
		//	1.	Empty value
		
		if ( empty($value) ) {
			
			$this->setHtml('value', '');
		
			if ( $empty_text = $this->getOption('empty_text') && is_string($empty_text) )
				$this->setHtml('text', $empty_text);
						
			// if field has a "message" 
			elseif ( $text = $this->fieldProp('message') )
				$this->setHtml('text', $text);	
			
			else
				$this->setHtml('text', 'Edit');
		
		}
		else {
				
			$this->setHtml('value', $value);
			
			if ( 1 == $value )		
				$this->setHtml('text', 'Yes');
		
			elseif ( 0 == $value )
				$this->setHtml('text', 'No');
			
		}
	}

}

?>