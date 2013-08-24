<?php
/** Generic Template Tag **/

function xe_the_field( $type, $field_name, $object_id, $args = array() ) {
	
	if ( X_EDITABLE_ACF_ENABLE ) {
	
		$field_class = 'X_Editable_ACF_' . ucfirst($type);
		
		if ( ! class_exists($field_class) )
			return "Field class {$field_class} does not exist";
		
		$field = new $field_class($field_name, $object_id, $args);
	}
	else {
		
		$args['input_type'] = strtolower($type);
		
		$field = new X_Editable_Meta($object_id, $field_name, $args);
	}
	
	extract($args);
	
	if ( isset($input_type) && $input_type )
		$field->set_input_type($input_type);
	
	if ( isset($data) && is_array($data) ) {
		foreach($data as $d => $v) :
			$field->addDataArg($d, $v);
		endforeach;
	}
	
	if ( isset($empty_text) && $empty_text )
		$field->setOption('empty_text', $empty_text);
	
	if ( isset($decimals) && ! is_null($decimals) )
		$field->setOption('decimals', $decimals);
	
	if ( isset($show_label) && $show_label )
		$field->showLabel();	
	
	if ( isset($external) && $external ) {
		
		if ( isset($edit_link) && $edit_link ) {
			$field->addDataArg(array('external' => true, 'edit_link' => true));
			$field->html();
			$field->showValues($values_as_ul);
		}
		else {
			$field->showValues($values_as_ul);	
			$field->html();
		}
	}
	else
		$field->html();
}

// wrapper for xe_the_field()
function editable_field($type, $field_name, $object_id, $args = array() ){
	xe_the_field($type, $field_name, $object_id, $args);	
}


?>