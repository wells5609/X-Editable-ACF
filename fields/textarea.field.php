<?php

class XE_ACF_Textarea extends XE_ACF_Field {
	
	
	function __construct( $field_name, $object_id, $object_name ) {
		
		// don't remove
		parent::__construct($field_name, $object_id, $object_name);
			
		/* Field-specific args */
		
		$this->add_data_arg('rows', 6);
		$this->add_data_arg('autoText', 'never');
		$this->add_data_arg('mode', 'inline');
		$this->add_data_arg('display', "false"); // quote booleans so they actually print
		
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
	
	// set the input type to use (in this case, not depending on value)
	function set_input_type() {
		
		$this->options['input_type'] = 'textarea';
		
	}
	
	// sets value and text for X-Editable element attributes
	function set_value_and_text() {
		
		$value = $this->field['value'];
		
		//	1.	Empty value
		
		if ( empty($value) ) :
			$this->html['value'] = '';
			$this->html['text'] = 'Edit';	
		
				
		//	2.	Multiple Values
		
		else :
		
			$this->html['value'] = $this->field['value'];
			$this->html['text'] = "Edit";
		
		endif;
		
	}

}


/* Template tags */

function xe_textarea( $field_name, $object_id, $args = array(), $object_name = false ) {
	
	extract($args);
	
	$textarea = new XE_ACF_Textarea($field_name, $object_id, $object_name);
	
	if ( $data ) {
		foreach($data as $d => $v) :
			$textarea->add_data_arg($d, $v);
		endforeach;
	}
	
	if ( $show_label ) {
		$textarea->show_label();	
	}
		
	$textarea->show_values();	
	
	$textarea->html();
	
}


?>