<?php
/** Generic Template Tag **/

function xe_the_field( $type, $field_name, $object_id, $args = array() ) {
		
	$field_class = 'X_Editable_ACF_' . ucfirst($type);
	
	if ( ! class_exists($field_class) )
		throw new Exception("Field class {$field_class} does not exist");
	
	$field = new $field_class($field_name, $object_id, $args);
	
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

?>