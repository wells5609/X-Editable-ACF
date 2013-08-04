<?php

class X_Editable_ACF_Taxonomy extends X_Editable_ACF_Field {
	
	// custom field var
	var $valid_inputs;
	
	
	function __construct( $field_name, $object_id, $args = array() ) {
		
		/* parental construction */
		parent::__construct($field_name, $object_id, $args = array() );
		
		/* Field-specific args */
		$this->addDataArg('taxonomy', $this->meta['taxonomy']);
	
		/* custom input validation */
		$valid_inputs = array(
			'checklist',
			'select',
		);
		// Themes/plugins can add more inputs here
		$this->valid_inputs = apply_filters('xe/valid_inputs/type=' . $this->meta['type'], $valid_inputs, $this->meta);
		
		/* Filters */
		$format = $this->meta['return_format'];
		
		$this->set_input_type();
		
		
		// return_format determines external text output
		add_filter('xe/external/text/type=taxonomy/format=id', array($this, 'external_text_id'), 10, 2);
		
		add_filter('xe/external/text/type=taxonomy/format=object', array($this, 'external_text_object'), 10, 2);
		
	}
	
	// Return items as array (text of external values output)
	function external_text_id( $field_value, $field ) {
		
		// array value
		if ( is_array($field_value) ) {
	
			$return = array();
	
			foreach($field_value as $val) :
				
				$term = get_term_by('id', $val, $field['taxonomy']);
						
				$return[] = $term->name;
								
			endforeach;
			
			return $return;
		}
		
		// non-empty, non-array value
		elseif ( !empty($field_value) ) {
			
			$term = get_term_by('id', $field_value, $field['taxonomy']);
					
			return $term->name;
		}
				
	}
	
	// format external display text for "object" return_format
	function external_text_object( $field_value, $field ) {
		
		
		if ( strstr($this->html['text'], ',') ) {
			
			$strings = @explode(',', $this->html['text']);
			$words = array();
			foreach($strings as $string){
				$words[] = trim($string);	
			}
			
			return $words;
			
		}
		elseif ( !empty($this->html['text']) ){
			return $this->html['text'];	
		}
		
	/*
		// array value
		if ( is_array($field_value) ) {
		
			$return = array();
		
			foreach($field_value as $val) :
				$return[] = $val->name;
				vardump($val);
			endforeach;
			
			return $return;
			
		}
		elseif ( !empty($field_value) ) {
			return $field_value->name;			
		}
	*/
	
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

			$this->setOption('input_type', $input);	
	
		}
		else {
			
			if ( $this->is_single_value() || ( isset($this->meta['value']) && ! is_array($this->meta['value']) ) ) {
				$this->setOption('input_type', 'select');	
			}
			
			else {
				$this->setOption('input_type', $this->valid_inputs[0]); // checklist
			}
			
		}
		
	}
	
	// sets value and text for X-Editable element attributes
	function set_value_and_text() {
		
		$value = $this->meta['value'];
		$format = $this->meta['return_format'];
		
		//	1.	Empty value
		
		if ( empty($value) ) :
		
			$this->setHtml('value', '');
			$this->setHtml('text', '<em>Empty</em>');		
						
		
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
					
					$term = get_term_by('id', $val, $this->meta['taxonomy']);
					
					$valueArray[] = $term->term_id;
					$textArray[] = $term->name;
					
				}

			endforeach;
			
			$this->setHtml('value', implode(',', $valueArray));
			$this->setHtml('text', implode(', ', $textArray));
			
			
		//	3.	One value
		
		else :
			
			if ( 'object' === $format ) {
				
				$this->setHtml('value', $value->term_id);
				$this->setHtml('text', $value->name);
				
			}
			elseif ( 'id' === $format ) {
				
				$term = get_term_by('id', $value, $this->meta['taxonomy']);
				
				$this->setHtml('value', $value);
				$this->setHtml('text', $term->name);
				
			}
			else {
				$this->setHtml('text', 'Something went horribly wrong.');		
			}

		endif;
		
	}

}


/* Template tags */

function xe_taxonomy( $field_name, $object_id, $args = array() ) {
	
	xe_the_field('Taxonomy', $field_name, $object_id, $args);
		
}

?>