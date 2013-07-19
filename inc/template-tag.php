<?php
/** Generic Template Tag **/

function xe_the_field( $type, $field_name, $object_id, $args = array() ) {
		
	$field_class = 'XE_ACF_' . ucfirst($type);
	
	if ( ! class_exists($field_class) ) {
		throw new Exception("Field class $field_class does not exist");	
	}
	
	if ( ! $args['object_name'] ) {
		$args['object_name'] = false;
	}
	
	extract($args);
	
	$field = new $field_class($field_name, $object_id, $object_name);
	
	if ( $input_type ) {
		$field->set_input_type($input_type);	
	}
	
	if ( $data ) {
		foreach($data as $d => $v) :
			$field->add_data_arg($d, $v);
		endforeach;
	}
	
	if ( $empty_text ) {
		$field->set_option('empty_text', $empty_text);	
	}
	
	if ( isset($decimals) && ! is_null($decimals) ) {
		$field->set_option('decimals', $decimals);	
	}
	
	if ( $show_label ) {
		$field->show_label();	
	}
	
	if ( $external ) {
		
		if ( $edit_link ) {
			$field->add_data_arg('external', true);
			$field->html();
			$field->show_values($values_as_ul);
		}
		else {
			$field->show_values($values_as_ul);	
			$field->html();
		}
		
	}
	else {
		$field->html();	
	}
	
}

?>