<?php

class X_Editable_ACF_Date extends X_Editable_ACF_Field {
	
	
	function __construct( $field_name, $object_id, $args = array() ) {
		
		// don't remove
		parent::__construct($field_name, $object_id, $args);
		
		// add filters
		add_filter('xe/external/text/type='. $this->fieldProp('type'), array($this, 'external_text'), 10, 2);
		
		$this->set_input_type('date');
				
		// add other stuffs
		$this->addDataArg('format', 'yyyy-mm-dd');
		$this->addDataArg('viewformat', 'M d, yyyy');
		
	}
	
	
	// filter applied in X_Editable_ACF_Functions::create_external()
	function external_text( $field_value, $field ) {
		
		if ( empty($field_value) ) {
			return 'Empty';
		}
		else {
			return $field_value;				
		}
		
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
			$this->setHtml('text', '<em>Empty</em>');	
		
				
		//	2.	Values
		
		else :
			
			$this->setHtml('value', $value);
			$this->setHtml('text', $value);	
		
		endif;
		
	}

}


/* Template tags */

function xe_date( $field_name, $object_id, $args = array() ) {
	
	// Never show external date values as ul
	if ( isset($args['external']) && $args['external'] ) {
		$args['values_as_ul'] = false;	
	}
	
	xe_the_field('Date', $field_name, $object_id, $args);
		
}


?>