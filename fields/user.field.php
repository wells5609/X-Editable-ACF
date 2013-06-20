<?php

class XE_ACF_User extends XE_ACF_Field {
	
	
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
			
			$return = $field_value['display_name'];				
		
		}
		
		return $return;
		
	}
	
		
	/** =======================
		Redefined functions 
		(from parent class)
	======================== */
	
	// set the input type to use
	function set_input_type() {
		
		if ( 'select' === $this->field['field_type'] ) {
			$this->set_option('input_type', 'select');
		}
		else {
			$this->set_option('input_type', 'checklist');
		}
		
	}
	
	// sets value and text for X-Editable element attributes
	function set_value_and_text() {
		
		$value = $this->field['value'];
		
		//	1.	Empty value
		
		if ( empty($value) ) :
		
			$this->set_html('value', '');
			$this->set_html('text', 'Edit');	
		
				
		//	2.	Has Value
		
		else :
			// should always be an array
			if ( isset($value) && is_array($value) ) {
								
				// if we have an array of WP_User object arrays
				if ( is_array($value[0]) ) {
						
					$arrayValues = array();
					$arrayText = array();

					foreach($value as $val) :						
						$arrayValues[] = $val['ID'];
						$arrayText[] = $val['display_name'];
					endforeach;
						
					$html_value = implode(',', $arrayValues);
					$html_text = implode(',', $arrayText);
					
				}
				
				// we just have 1 array - the WP_User object
				else {
					
					$html_value = $value['ID'];
					$html_text = $value['display_name'];
						
				}
										
			}
			
			$this->set_html('value', $html_value);
			$this->set_html('text', $html_text);
		
		endif;
		
	}

}


/* Template tags */

function xe_user( $field_name, $object_id, $args = array(), $object_name = false ) {
	
	extract($args);
	
	$userfield = new XE_ACF_User($field_name, $object_id, $object_name);
	
	if ( $data ) {
		foreach($data as $d => $v) :
			$userfield->add_data_arg($d, $v);
		endforeach;
	}
	
	if ( $show_label ) {
		$userfield->show_label();	
	}
	
	if ( $external ) {
		
		if ( $edit_button ) {
			$userfield->add_data_arg('external', true);
			$userfield->html();
			$userfield->show_values($values_as_ul);
		}
		else {
			$userfield->show_values($values_as_ul);	
			$userfield->html();
		}
		
	}
	else {
		$userfield->html();	
	}
	
}


?>