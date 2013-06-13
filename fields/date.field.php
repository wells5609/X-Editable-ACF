<?php

class XE_ACF_Date extends XE_ACF_Field {
	
	
	function __construct( $field_name, $object_id ) {
		
		// don't remove
		parent::__construct($field_name, $object_id);
		
		// add filters
		add_filter('xe/external/text/type='. $this->field['type'], array($this, 'external_text'), 10, 2);
					
		// add other stuffs
		$this->add_data_arg('format', 'yyyy-mm-dd');
		$this->add_data_arg('viewformat', 'M d, yyyy');
		
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
	
	// set the input type to use (in this case, not depending on value)
	function set_input_type() {
		
		$this->options['input_type'] = 'date';
		
	}
	
	// sets value and text for X-Editable element attributes
	function set_value_and_text() {
		
		$value = $this->field['value'];
		
		//	1.	Empty value
		
		if ( empty($value) ) :
			$this->html['value'] = '';
			$this->html['text'] = 'Empty';	
		
				
		//	2.	Values
		
		else :
		
			$this->html['value'] = $this->field['value'];
			$this->html['text'] = $this->field['value'];
		
		endif;
		
	}

}


/* Template tags */

function xe_date( $field_name, $object_id, $args = array() ) {
	
	$date = new XE_ACF_Date($field_name, $object_id);
	
	extract($args);
	
	if ( $show_label )
		$date->show_label();	

	if ( $show_values ) 
		$date->show_values(false);
	
	$date->html();
	
}


?>