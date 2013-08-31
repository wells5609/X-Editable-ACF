<?php
/**
* @package X-Editable-ACF
*/

class X_Editable_ACF_Text extends X_Editable_ACF_Field {
	
	
	function __construct( $field_name, $object_id, $args = array() ) {
		
		// don't remove
		parent::__construct($field_name, $object_id, $args);
		
		/* Filters */
		add_filter('xe/external/text/type='. $this->meta['type'], array(&$this, 'external_text'), 10, 2);
		
	}
	
	
	/**
	* 	Sets text to display in "external_values"	
	*
	*	Filter applied in X_Editable_ACF_Functions::create_external()
	*/
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
	
	/** 
	*	Set the input type to use - in this case, not depending on the value.
	* 
	*	Called at the end of X_Editable_ACF_Field::__construct()
	*/
		function set_input_type() {
			
			$this->setOption('input_type', 'text');
			
		}
	
	/**
	*	Sets value and text for X-Editable element attributes
	*
	*/
	function set_value_and_text() {
		
		$value = $this->meta['value'];
		
		if ( empty($value) ) {
			$this->setHtml('value', '');
			$this->setHtml('text', '<em>Empty</em>');	
		}
		else {
			$this->setHtml('value', $value);
			$this->setHtml('text', $value);
		}
		
	}

}


// Template tag
function xe_text( $field_name, $object_id, $args = array() ) {
	
	xe_the_field('Text', $field_name, $object_id, $args);
}


?>