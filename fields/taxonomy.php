<?php
/**
* @package X-Editable-ACF
*/

class X_Editable_ACF_Taxonomy extends X_Editable_ACF_Field {
	
	// custom field var
	var $valid_inputs = array(
		'checklist',
		'select',
	);
	
	function __construct( $field_name, $object_id, $args = array() ) {
		
		parent::__construct($field_name, $object_id, $args = array());
		
		/* Field-specific args */
		$this->addDataArg('taxonomy', $this->meta['taxonomy']);
	
		/* custom input validation */
		$this->valid_inputs = apply_filters('xe/valid_inputs/type=' . $this->meta['type'], $this->valid_inputs, $this->meta);
		
		/* Filters */
		
		// return_format determines external text output
		add_filter('xe/external/text/type=taxonomy/format=id', array(&$this, 'external_text_id'), 10, 2);
		add_filter('xe/external/text/type=taxonomy/format=object', array(&$this, 'external_text_object'), 10, 2);
		
	}
	
	// Return items as array (text of external values output)
	function external_text_id( $field_value, $field ) {
		
		// array value
		if ( is_array($field_value) ) {
	
			$termText = array();
	
			foreach($field_value as $val) :
				
				$term = get_term_by('id', $val, $field['taxonomy']);
						
				$termText[] = $term->name;
								
			endforeach;
			
			return $termText;
		}
		
		// non-empty, non-array value
		elseif ( !empty($field_value) ) {
			
			$term = get_term_by('id', $field_value, $field['taxonomy']);
					
			return $term->name;
		}
				
	}
	
	/**
	* 	Sets text to display in "external_values" when return_format is object
	*	Filter applied in X_Editable_ACF_Functions::create_external()
	*
	*	Notice how the text here is set from the element text (not the field value passed)
	*/
	function external_text_object( $field_value, $field ) {
		
		if ( strstr($this->html['text'], ',') ) {
			$words = array();
			$strings = @explode(',', $this->html['text']);
			
			foreach($strings as $string)
				$words[] = @trim($string);	
			
			return $words;
		}
		
		elseif ( !empty($this->html['text']) )
			return $this->html['text'];
	}
	
	
	/** =======================
		Redefined functions 
		(from parent class)
	======================== */
	
	/** set_input_type
	*
	*	Set the input type to use depending on value |OR| use your own custom defaults.
	* 	This is largely illustrative and could probably be coded better
	*/
	function set_input_type( $input = NULL) {
		
		if ( NULL !== $input && in_array($input, $this->valid_inputs) )
			$this->setOption('input_type', $input);	
	
		elseif ($this->is_single_value() 
			|| (isset($this->meta['value']) && ! is_array($this->meta['value'])) )
				$this->setOption('input_type', 'select');
		
		else
			$this->setOption('input_type', $this->valid_inputs[0]); // 1st valid input: checklist
	
	}
	
	// sets value and text for X-Editable element attributes
	function set_value_and_text() {
		
		$value = $this->meta['value'];
		$format = $this->meta['return_format'];
	
		if ( empty($value) ){
			$this->setHtml('value', '');
			$this->setHtml('text', '<em>Empty</em>');		
		}
		elseif (is_array($value)) {
			
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
		}
		elseif ( 'object' === $format ) {	
			$this->setHtml('value', $value->term_id);
			$this->setHtml('text', $value->name);
		}
		elseif ( 'id' === $format ) {
			
			$term = get_term_by('id', $value, $this->meta['taxonomy']);
			
			$this->setHtml('value', $value);
			$this->setHtml('text', $term->name);
		}
	}

}

// Template tag
function xe_taxonomy( $field_name, $object_id, $args = array() ) {
	
	xe_the_field('Taxonomy', $field_name, $object_id, $args);
}

?>