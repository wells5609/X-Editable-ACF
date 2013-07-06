<?php

class XE_ACF_Taxonomy extends XE_ACF_Field {
	
	// custom field var
	var $valid_inputs;
	
	
	function __construct( $field_name, $object_id, $object_name ) {
		
		/* parental construction */
		parent::__construct($field_name, $object_id, $object_name);
		
		/* Field-specific args */
		$this->add_data_arg('taxonomy', $this->field['taxonomy']);
	
		/* custom input validation */
		$valid_inputs = array(
			'checklist',
			'select',
		);
		// Themes/plugins can add more inputs here
		$this->valid_inputs = apply_filters('xe/valid_inputs/type=' . $this->field['type'], $valid_inputs, $this->field);
		
		/* Filters */
		$format = $this->field['return_format'];
		// return_format determines external text output
		add_filter('xe/external/text/type=' . $this->field['type'] . '/format=' . $format, array($this, 'external_text_' . $format), 10, 2);
		
	}
	
	// Return items as array (text of external values output)
	function external_text_id( $field_value, $field ) {
		
		$return = array();
		
		// array value
		if ( is_array($field_value) ) {
			
			foreach($field_value as $val) :
				
				$term = get_term_by('id', $val, $field['taxonomy']);
						
				$return[] = $term->name;
								
			endforeach;
			
		}
		// non-empty, non-array value
		elseif ( !empty($field_value) ) {
			
			$term = get_term_by('id', $field_value, $field['taxonomy']);
					
			$return = $term->name;				
	
		}
		
		return $return;
		
	}
	
	// format external display text for "object" return_format
	function external_text_object( $field_value, $field ) {
		
		$return = array();
		
		// array value
		if ( is_array($field_value) ) {
			
			foreach($field_value as $val) :
				$return[] = $val->name;
			endforeach;
			
		}
		elseif ( !empty($field_value) ) {
			$return = $field_value->name;			
		}
		
		return $return;
		
	}
	
	
	/** =======================
		Redefined functions 
		(from parent class)
	======================== */
	
	// set the input type to use, depending on value
	// 		OR use your own custom defaults.
	// this is just illustrative
	function set_input_type( $input = NULL) {
		
		if ( NULL !== $input && in_array($input, $this->valid_inputs) ) {

			$this->set_option('input_type', $input);	
	
		}
		else {
			
			if ( $this->is_single_value() || ( isset($this->field['value']) && ! is_array($this->field['value']) ) ) {
				$this->set_option('input_type', 'select');	
			}
			
			else {
				$this->set_option('input_type', $this->valid_inputs[0]); // checklist
			}
			
		}
		
	}
	
	// sets value and text for X-Editable element attributes
	function set_value_and_text() {
		
		$value = $this->field['value'];
		$format = $this->field['return_format'];
		
		//	1.	Empty value
		
		if ( empty($value) ) :
		
			$this->set_html('value', '');
			$this->set_html('text', '<em>Empty</em>');		
						
		
		//	2.	Multiple Values
		
		elseif ( is_array($value) ) :
			
			$valueArray = array();
			$textArray = array();
			
			foreach($value as $val) :
				
				// object return_format
				if ( 'object' === $format ) {
				
					$valueArray[] = $val->term_id;
					$textArray[] = $val->name;
				
				}
				
				// id return_format
				elseif ( 'id' === $format ) {
					
					$term = get_term_by('id', $val, $this->field['taxonomy']);
					
					$valueArray[] = $term->term_id;
					$textArray[] = $term->name;
					
				}

			endforeach;
			
			$this->set_html('value', implode(',', $valueArray));
			$this->set_html('text', implode(', ', $textArray));
			
			
		//	3.	One value
		
		else :
			
			if ( 'object' === $format ) {
				
				$this->set_html('value', $value->term_id);
				$this->set_html('text', $value->name);
				
			}
			elseif ( 'id' === $format ) {
				
				$term = get_term_by('id', $value, $this->field['taxonomy']);
				
				$this->set_html('value', $value);
				$this->set_html('text', $term->name);
				
			}
			else {
				$this->set_html('text', 'Something went horribly wrong.');		
			}

		endif;
		
	}

}


/* Template tags */

function xe_taxonomy( $field_name, $object_id, $args = array(), $object_name = false ) {
	
	$tax = new XE_ACF_Taxonomy($field_name, $object_id, $object_name);
	
	extract($args);
	
	if ( $input_type ) {
		$tax->set_input_type($input_type);	
	}
	
	if ( $data ) {
		foreach($data as $d => $v) :
			$tax->add_data_arg($d, $v);
		endforeach;
	}
	
	if ( $show_label ) {
		$tax->show_label();	
	}
	
	if ( $external ) {
		
		if ( $edit_button ) {
			$tax->add_data_arg('external', true);
			$tax->html();
			$tax->show_values($values_as_ul);
		}
		else {
			$tax->show_values($values_as_ul);	
			$tax->html();
		}
		
	}
	else {
		$tax->html();	
	}
		
}


?>