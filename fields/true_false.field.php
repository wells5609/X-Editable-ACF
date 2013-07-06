<?php

class XE_ACF_True_False extends XE_ACF_Field {
	
	
	function __construct( $field_name, $object_id, $object_name ) {
		
		// don't remove
		parent::__construct($field_name, $object_id, $object_name);
			
		/* Field-specific args */
		
		$this->valid_inputs = array(
			'select',
			'checklist',
		);
		
		// Custom setup functions
		$this->set_source();
		
		/* Filters */
		
		add_filter('xe/external/text/type='. $this->field['type'], array($this, 'external_text'), 10, 2);
		
	}
	
	
	// filter applied in X_Editable_ACF_Functions::create_external()
	function external_text( $field_value, $field ) {
		
		if ( empty($field_value) ) {
			$return = 'Empty';
		}
		elseif ( 1 == $field_value ) {
			$return = 'Yes';
		}
		elseif ( 0 == $field_value ) {
			$return = 'No';	
		}
		
		return $return;
		
	}
	
	
	// Called in constructor - sets data-source for element
	private function set_source() {
		
		$source = array(
			1 => 'Yes',
			0 => 'No',
		);
		
		$this->set_html('source', json_encode($source, JSON_FORCE_OBJECT));
		
	}
		
	/** =======================
		Redefined functions 
		(from parent class)
	======================== */
	
	function set_input_type($input_type = NULL) {
		
		if ( ! is_null($input_type) && in_array($input_type, $this->valid_inputs) ) {
			$input = $input_type;	
		}
		else {
			$input = $this->valid_inputs[0];	
		}
		
		$this->set_option('input_type', $input);
			
	}
	
	
	// sets value and text for X-Editable element attributes
	function set_value_and_text() {
		
		$value = $this->field['value'];
		
		//	1.	Empty value
		
		if ( empty($value) ) :
		
			$empty_text = $this->get_option('empty_text');
			
			if ( $empty_text ) {
				if ( ! is_bool($empty_text) ) {
					$this->set_html('text', $empty_text);
				}
				elseif ( $text = $this->field['message'] ) {
					$this->set_html('text', $text);
				}
			}
			else {
				$this->set_html('text', 'Edit');	
			}
			$this->set_html('value', '');
			
				
		//	2.	Has Value
		
		else :
				
			$this->set_html('value', $value);
			
			if ( 1 == $value ) {
				
				$this->set_html('text', 'Yes');
			
			}
			elseif ( 0 == $value ) {
				$this->set_html('text', 'No');
			}
			
		endif;
		
	}

}


/* Template tags */

function xe_true_false( $field_name, $object_id, $args = array(), $object_name = false ) {
	
	$true_false = new XE_ACF_True_False($field_name, $object_id, $object_name);
	
	extract($args);
	
	if ( $data ) {
		foreach($data as $d => $v) :
			$true_false->add_data_arg($d, $v);
		endforeach;
	}
	
	if ( $show_label ) {
		$true_false->show_label();	
	}
	
	if ( $input_type ) {
		$true_false->set_input_type($input_type);	
	}
	
	if ( $empty_text ) {
		$true_false->set_option('empty_text', $empty_text);	
	}
	
	if ( $external ) {
		
		if ( $edit_button ) {
			$true_false->add_data_arg('external', true);
			$true_false->html();
			$true_false->show_values();
		}
		else {
			$true_false->show_values();	
			$true_false->html();
		}
		
	}
	else {
		$true_false->html();	
	}
	
}


?>