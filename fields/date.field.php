<?php

class XE_ACF_Date extends XE_ACF_Field {
	
	
	function __construct( $field_name, $object_id, $object_name ) {
		
		// don't remove
		parent::__construct($field_name, $object_id, $object_name);
		
		// add filters
		add_filter('xe/external/text/type='. $this->field['type'], array($this, 'external_text'), 10, 2);
		
		$this->set_input_type('date');
				
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
	
	// sets value and text for X-Editable element attributes
	function set_value_and_text() {
		
		$value = $this->field['value'];
		
		//	1.	Empty value
		
		if ( empty($value) ) :
			$this->set_html('value', '');
			$this->set_html('text', '<em>Empty</em>');	
		
				
		//	2.	Values
		
		else :
			
			$this->set_html('value', $value);
			$this->set_html('text', $value);	
		
		endif;
		
	}

}


/* Template tags */

function xe_date( $field_name, $object_id, $args = array(), $object_name = false ) {
	
	$date = new XE_ACF_Date($field_name, $object_id, $object_name);
	
	extract($args);
	
	if ( $data ) {
		foreach($data as $d => $v) :
			$date->add_data_arg($d, $v);
		endforeach;
	}
	
	if ( $show_label )
		$date->show_label();	

	if ( $show_values ) 
		$date->show_values(false); // don't use ul
	
	$date->html();
	
}


?>