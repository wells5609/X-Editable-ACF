<?php
/** Template Tags */

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


/** editable_field
* wrapper for xe_the_field()
*
* @param	string	$type			The type of field, from class name (e.g. 'Date', 'True_False', 'Taxonomy')
* @param	string	$field_name		The name of the field
* @param	int		$object_id		The ID of the object being edited (e.g. post ID, user ID)
* @param	array	$args			Arguments for X-Editable field generation (e.g. 'input_type', 'show_label', 'external')
* @return	string|void				HTML markup for X-Editable field
*/
function editable_field($type, $field_name, $object_id, $args = array() ){
	xe_the_field($type, $field_name, $object_id, $args);	
}


/** 
*	Template tags for built-in fields
*	Relocated here so autoloading works.
*/

// TEXT
function xe_text( $field_name, $object_id, $args = array() ) {
	xe_the_field('Text', $field_name, $object_id, $args);
}
// TAXONOMY
function xe_taxonomy( $field_name, $object_id, $args = array() ) {
	xe_the_field('Taxonomy', $field_name, $object_id, $args);
}
// DATE
function xe_date( $field_name, $object_id, $args = array() ) {
	// Never show external date values as ul
	if ( isset($args['external']) && $args['external'] )
		$args['values_as_ul'] = false;
	xe_the_field('Date', $field_name, $object_id, $args);		
}
// NUMBER
function xe_number( $field_name, $object_id, $args = array() ) {
	xe_the_field('Number', $field_name, $object_id, $args);
}
// SELECT
function xe_select( $field_name, $object_id, $args = array() ) {	
	xe_the_field('Select', $field_name, $object_id, $args);		
}
// TRUE_FALSE
function xe_true_false( $field_name, $object_id, $args = array() ) {	
	xe_the_field('True_False', $field_name, $object_id, $args);
}
// TEXTAREA
function xe_textarea( $field_name, $object_id, $args = array() ) {	
	xe_the_field('Textarea', $field_name, $object_id, $args);
}
// USER
function xe_user( $field_name, $object_id, $args = array()) {
	xe_the_field('User', $field_name, $object_id, $args);
}
?>