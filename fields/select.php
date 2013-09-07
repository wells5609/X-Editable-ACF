<?php
/**
* @package X-Editable-ACF
*/

class X_Editable_ACF_Select extends X_Editable_ACF_Field {
	
	function __construct( $field_name, $object_id, $args = array() ) {
		
		// don't remove
		parent::__construct($field_name, $object_id, $args);
			
		// Field-specific args
		$this->set_input_type('select');
		
		// Filters
		add_filter('xe/external/text/type='. $this->fieldProp('type'), array($this, 'external_text'), 10, 2);
		
	}
	
	
	// filter applied in X_Editable_Functions::create_external()
	function external_text( $field_value, $field ) {
		if ( empty($field_value) )
			return 'Empty';
		else
			return $field_value;
	}
	
		
	/** =======================
		Redefined functions 
		(from parent class)
	======================== */
	
	// sets value and text for X-Editable element attributes
	function set_value_and_text() {
		
		$value = $this->meta['value'];
		
		if ( empty($value) ) {
			$this->setHtml('value', '');
			$this->setHtml('text', 'Edit');	
		}
		else {
				
			$this->setHtml('value', $value);
			
			if ( $this->meta['choices'][$value] )
				$this->setHtml('text', $this->meta['choices'][$value]);
			
			else 
				$this->setHtml('text', $value);
		}
	}

}

?>