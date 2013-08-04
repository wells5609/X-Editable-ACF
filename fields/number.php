<?php

class X_Editable_ACF_Number extends X_Editable_ACF_Field {
	
	
	function __construct( $field_name, $object_id, $args = array() ) {
		
		$this->set_input_type('number');
		
		// don't remove
		parent::__construct($field_name, $object_id, $args = array());
			
		/* Field-specific args */
		
		/* Filters */
		
		add_filter('xe/external/text/type='. $this->fieldProp('type'), array($this, 'external_text'), 10, 2);
		
		/* defaults for custom option */
		
		$this->set_decimals(2);
			
	}
	
	// custom method
	function set_decimals($integer) {
		
		$this->setOption('decimals', $integer);
			
	}
	
	
	// filter applied in X_Editable_ACF_Functions::create_external()
	function external_text( $field_value, $field ) {
		
		if ( empty($field_value) ) {
			
			$return = 'Empty';
			
		}
		elseif ( is_numeric($field_value) ) {
			
			$return = number_format($field_value, $this->getOption('decimals'));				
		
		}
		
		return $return;
		
	}
	
		
	/** =======================
		Redefined functions 
		(from parent class)
	======================== */
	
	
	// sets value and text for X-Editable element attributes
	function set_value_and_text() {
		
		$value = $this->fieldProp('value');
		
		//	1.	Empty value
		
		if ( empty($value) ) :
			$this->setHtml('value', '');
			$this->setHtml('text', 'Edit');	
		
				
		//	2.	has value
		
		else :
		
			$this->setHtml('value', $value);
			
			if ( is_numeric($value) ) {
				
				$this->setHtml('text', number_format($value, $this->getOption('decimals')));
			
			}
			else {
				$this->setHtml('text', $value);	
			}
			
		endif;
		
	}

}


/* Template tags */

function xe_number( $field_name, $object_id, $args = array() ) {
	
	xe_the_field('Number', $field_name, $object_id, $args);
	
}


?>