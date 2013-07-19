<?php

class XE_ACF_Number extends XE_ACF_Field {
	
	
	function __construct( $field_name, $object_id, $object_name ) {
		
		$this->set_input_type('number');
		
		// don't remove
		parent::__construct($field_name, $object_id, $object_name);
			
		/* Field-specific args */
		
		/* Filters */
		
		add_filter('xe/external/text/type='. $this->field['type'], array($this, 'external_text'), 10, 2);
		
		/* defaults for custom option */
		
		$this->set_option('decimals', $integer);
			
	}
	
	// custom method
	function set_decimals($integer) {
		
		$this->set_option('decimals', $integer);
			
	}
	
	
	// filter applied in X_Editable_ACF_Functions::create_external()
	function external_text( $field_value, $field ) {
		
		if ( empty($field_value) ) {
			
			$return = 'Empty';
			
		}
		elseif ( is_numeric($field_value) ) {
			
			$return = number_format($field_value, $this->options['decimals']);				
		
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
		
				
		//	2.	has value
		
		else :
		
			$this->set_html('value', $value);
			
			if ( is_numeric($value) ) {
				
				$this->set_html('text', number_format($value, $this->options['decimals']));
			
			}
			else {
				$this->set_html('text', $value);	
			}
			
		endif;
		
	}

}


/* Template tags */

function xe_number( $field_name, $object_id, $args = array() ) {
	
	xe_the_field('Number', $field_name, $object_id, $args);
	
}


?>