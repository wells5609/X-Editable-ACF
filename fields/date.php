<?php
/**
* @package X-Editable-ACF
*/

class X_Editable_ACF_Date extends X_Editable_ACF_Field {
	
	function __construct( $field_name, $object_id, $args = array() ) {
		
		// don't remove
		parent::__construct($field_name, $object_id, $args);
		
		// add filters
		add_filter('xe/external/text/type='. $this->fieldProp('type'), array($this, 'external_text'), 10, 2);
				
		// add other stuffs
		$this->addDataArg('format', 'yyyy-mm-dd');
		$this->addDataArg('viewformat', 'M d, yyyy');
		
	}
	
		
	// filter applied in X_Editable_ACF_Functions::create_external()
	function external_text( $field_value, $field ) {
		
		if ( empty($field_value) ) {
			return 'Empty';
		}
		else {
			return $field_value;				
		}
		
	}
	
		
	/** =======================
		Redefined functions 
		(from parent class)
	======================== */
	
	// sets value and text for X-Editable element attributes
	function set_value_and_text() {
		
		$value = $this->fieldProp('value');
		
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

?>