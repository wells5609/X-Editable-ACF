<?php

class XE_ACF_Select extends XE_ACF_Field {
	
	
	function __construct( $field_name, $object_id, $object_name ) {
		
		// don't remove
		parent::__construct($field_name, $object_id, $object_name);
			
		/* Field-specific args */
		
		$this->set_input_type('select');
		
		/* Filters */
		
		add_filter('xe/external/text/type='. $this->field['type'], array($this, 'external_text'), 10, 2);
		
	}
	
	
	// filter applied in X_Editable_ACF_Functions::create_external()
	function external_text( $field_value, $field ) {
		
		if ( empty($field_value) ) {
			$return = 'Empty';
		}
		else {
			$return = $field_value;
		}
		
		return $return;
		
	}
	
		
	/** =======================
		Redefined functions 
		(from parent class)
	======================== */
	
	
	// sets value and text for X-Editable element attributes
	function set_value_and_text() {
		
		$value = $this->field['value'];
		
		//	1.	Empty value
		
		if ( empty($value) ) :
			$this->set_html('value', '');
			$this->set_html('text', 'Edit');	
		
				
		//	2.	Has Value
		
		else :
				
			$this->set_html('value', $value);
			
			if ( $this->field['choices'][$value] ) {
				
				$this->set_html('text', $this->field['choices'][$value]);
			
			}
			else {
				$this->set_html('text', $value);
			}
			
		endif;
		
	}

}


/* Template tags */

function xe_select( $field_name, $object_id, $args = array() ) {
	
	xe_the_field('Select', $field_name, $object_id, $args);
		
}


?>