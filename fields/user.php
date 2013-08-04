<?php

class X_Editable_ACF_User extends X_Editable_ACF_Field {
	
	
	function __construct( $field_name, $object_id, $object_name ) {
		
		// don't remove
		parent::__construct($field_name, $object_id, $object_name);
			
		/* Field-specific args */
		
		
		/* Filters */
		
		add_filter('xe/external/text/type='. $this->fieldProp('type'), array($this, 'external_text'), 10, 2);
		
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
		
		if ( 'select' === $this->fieldProp('field_type') ) {
			$this->setOption('input_type', 'select');
		}
		else {
			$this->setOption('input_type', 'checklist');
		}
		
	}
	
	// sets value and text for X-Editable element attributes
	function set_value_and_text() {
		
		$value = $this->fieldProp('value');
		
		//	1.	Empty value
		
		if ( empty($value) ) :
		
			$this->setHtml('value', '');
			$this->setHtml('text', 'Edit');	
		
				
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
			
			$this->setHtml('value', $html_value);
			$this->setHtml('text', $html_text);
		
		endif;
		
	}

}


/* Template tags */

function xe_user( $field_name, $object_id, $args = array()) {
	
	xe_the_field('User', $field_name, $object_id, $args);
	
}


?>