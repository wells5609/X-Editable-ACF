<?php

class XE_ACF_Select extends XE_ACF_Field {
	
	
	function __construct( $field_name, $object_id, $object_name ) {
		
		// don't remove
		parent::__construct($field_name, $object_id, $object_name);
			
		/* Field-specific args */
		
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
		
		$this->options['input_type'] = 'select';
		
	}
	
	// sets value and text for X-Editable element attributes
	function set_value_and_text() {
		
		$value = $this->field['value'];
		
		//	1.	Empty value
		
		if ( empty($value) ) :
			$this->html['value'] = '';
			$this->html['text'] = 'Edit';	
		
				
		//	2.	Has Value
		
		else :
				
			$this->html['value'] = $value;
			
			if ( $this->field['choices'][$value] ) {
				
				$this->html['text'] = $this->field['choices'][$value];
			
			}
			else {
				$this->html['text'] = $value;
			}
			
		endif;
		
	}

}


/* Template tags */

function xe_select( $field_name, $object_id, $args = array(), $object_name = false ) {
	
	$select = new XE_ACF_Select($field_name, $object_id, $object_name);
	
	extract($args);
	
	if ( $data ) {
		foreach($data as $d => $v) :
			$select->add_data_arg($d, $v);
		endforeach;
	}
	
	if ( $show_label ) {
		$select->show_label();	
	}
	
	if ( $external ) {
		
		if ( $edit_button ) {
			$select->add_data_arg('external', true);
			$select->html();
			$select->show_values();
		}
		else {
			$select->show_values();	
			$select->html();
		}
		
	}
	else {
		$select->html();	
	}
	
}


?>