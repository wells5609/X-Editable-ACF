<?php

class X_Editable_AJAX_Callbacks {
	
	public static $NO_PRIV_CALLBACK = array(__CLASS__, 'must_log_in');
	
	public static function init() {
		
		$no_priv = apply_filters('xe/callbacks/no_priv', self::$NO_PRIV_CALLBACK);
				
		// Meta edit 
		add_action( 'wp_ajax_xeditable_meta_handler', array(__CLASS__, 'meta_handler') );
		add_action( 'wp_ajax_nopriv_xeditable_meta_handler', $no_priv );
		
		// Taxonomy edit
		add_action( 'wp_ajax_xeditable_tax_handler', array(__CLASS__, 'tax_handler') );
		add_action( 'wp_ajax_nopriv_xeditable_tax_handler', $no_priv );
		
		// Meta load
		add_action( 'wp_ajax_xeditable_meta_load', array(__CLASS__, 'load_meta') );
		add_action( 'wp_ajax_nopriv_xeditable_meta_load', $no_priv );
		
		// Term load
		add_action( 'wp_ajax_xeditable_term_load', array(__CLASS__, 'load_terms') );
		add_action( 'wp_ajax_nopriv_xeditable_term_load', $no_priv );
		
		// Gets taxonomy options (terms)
		add_action( 'wp_ajax_xeditable_tax_options', array(__CLASS__, 'tax_options') );
		add_action( 'wp_ajax_nopriv_xeditable_tax_options', $no_priv );
		
		// Gets user options (for meta)
		add_action( 'wp_ajax_xeditable_user_options', array(__CLASS__, 'user_options') );
		add_action( 'wp_ajax_nopriv_xeditable_user_options', $no_priv );
			
	}
	
	/* ===================
		AJAX HANDLERS
	=================== */
	
	// user not logged in
	public function must_log_in() {
		status_header('400');
		exit('You must log in.');
	}
	
	// Meta AJAX handler function
	public function meta_handler() {
	
		$object_id = $_POST['pk']; // post id.
		$name = trim($_POST['name']); // the (ACF) field/meta key
		$value = wp_filter_kses( trim($_POST['value']) ); // uses "data-value" if present, otherwise html contents.
		
		$object_name = trim($_POST['object_name']); // if we're editing Term, User, etc. (not set for Post)
		$acf_type = trim($_POST['acf_type']);
		$field_key = trim($_POST['key']);
		
		// verify nonce using name
		if ( ! wp_verify_nonce( $_POST['nonce'], $name )) {
			status_header('400');
			exit('Wise guy, huh?');
		}
		
		// Checking with is_null() allows us to post '0' or 'false' as the value.
		if ( is_null($value) ) {
			status_header('400');
			exit("Cannot post nothing.");
		}
		
		if ( has_action('xe/update_meta') ) {
			do_action('xe/update_meta', $_POST);
		}
		else {
			
			if ( function_exists('update_field') && $acf_type ) {
				
				// Prefix object_id with object type (taxonomy slug, etc.)
				if ( $object_name )
					$object_id = $object_name . '_' . $object_id;
				
				// use key to update, if possible
				if ( $field_key && ( $field_key !== $name ) ) {
					$name = 'field_' . $field_key;	
				}
				
				if ( 'user' === $acf_type )
					// forget why I did this but assume there was a reason
					update_field( $name, array($value), $object_id );
				else 
					update_field( $name, $value, $object_id );
			}
			else {
				
				if ( 'user' === $object_name )
					update_user_meta($object_id, $name, $value);
				else
					update_post_meta($object_id, $name, $value);
			}
		}
		print_r($_POST); // debug response
		die();	
	}
	
	
	// Taxonomy terms (add/remove to/from an object)
	public function tax_handler() {
		
		$object_id = (int) $_POST['pk']; // the POST ID.
		$name = trim($_POST['name']); // the field name - used for nonce
		$taxonomy = trim($_POST['tax']); // will be the taxonomy name
		$value = $_POST['value'];
		$single = $_POST['issingle'];
	
		if ( ! wp_verify_nonce( $_POST['nonce'], $name )) {
			status_header('400');
			exit('Wise guy, huh?');
		}
		
		if ( has_action('xe/update_tax') ) {
			do_action('xe/update_tax', $_POST);
		}
		elseif ( ! $value ) {
			// empty value, but ID and tax were passed => set to empty array and exit
			if ($object_id && $taxonomy)
				return wp_set_object_terms($object_id, array(), $taxonomy, $single);
			die;
		}
		else {
			
			$terms = array();
			
			if ( is_array($value) ) {
				foreach($value as $val) :
					
					$terms[] = process_term($val, $taxonomy);
					
				endforeach;
			}
			else {
				
				// if single, save value not in array
				if ( $single )
					$terms = process_term($value, $taxonomy);
								
				else
					$terms[] = process_term($value, $taxonomy);
			}
			
			wp_set_object_terms($object_id, $terms, $taxonomy, $single);
		}
		die();
	}
	
	
	// Handles GET requests for tax terms (used to populate dropdowns/checkboxes).
	public function tax_options() {
		
		$tax = trim($_REQUEST['tax']); 
		$string = $_REQUEST['string'];
		$hide_empty = $_REQUEST['hide_empty'];
		
		if ( is_null($tax) )
			return;
		
		if ( $hide_empty )
			$getTermArgs = array('hide_empty' => true);
		else
			$getTermArgs = array('hide_empty' => false);
		
		$getTermArgs = apply_filters('xe/tax_options/get_terms/args', $getTermArgs, $_REQUEST);
		
		$terms = get_terms($tax, $getTermArgs );
		
		if ( $terms ) {
			$options = array();
			foreach ($terms as $term) :
				// terms as string (for typeahead)
				if ( $string )
					$options[] = $term->name;
				// terms as JS object
				else
					$options[$term->term_id] = $term->name;
			endforeach;
			wp_send_json($options);
		}
	}
	
	
	// Handles GET request for users
	public function user_options() {
		
		$role = trim($_REQUEST['role']); 
		
		if ( ! is_null($role) )
			$users = get_users( 'role=' . $role );
		else 
			$users = get_users();
	
		if ( $users ) {
			$options = array();
			foreach ($users as $user) :
				$options[$user->ID] = $user->display_name;
			endforeach;
			wp_send_json($options);
		}
	}
	
	
	// Load terms on edit taxonomy success
	public function load_terms() {
		
		$tax = trim($_REQUEST['tax']);
		$object_id = $_REQUEST['object_id'];
		$as_ul = $_REQUEST['as_ul'];
		
		$terms = get_the_terms($object_id, $tax);
				
		if ( $terms && ! $as_ul ) {
			$term_names = array();
			foreach($terms as $term) :
				$term_names[] = $term->name;
			endforeach;
			$str = implode(', ', $term_names);
		}
		elseif ( $terms ) {
			$str = '<ul class="x-editable ajax-terms">';
			foreach($terms as $term) :
				$str .= '<li>' . $term->name . '</li>';
			endforeach;
			$str .= '</ul>';
		}
		die($str);	
	}
	
	
	// load meta (called on meta edit success)
	public function load_meta() {
		
		$post_id = $_REQUEST['post_id'];
		$field = trim($_REQUEST['field']);
		$object_name = trim($_REQUEST['object_name']);
		$single = $_REQUEST['single'];
		
		$value = false;
		
		if (function_exists('get_field')) {
			// Prefix object_id with object type (taxonomy name, etc.)
			if ( $object_name ) {
				$post_id = $object_name . '_' . $post_id;
			}
			
			$value = get_field($field, $post_id);
		}
		else if ( ! function_exists('get_field') || ! $value ) {
			
			if ( 'user' === $object_name )
				$value = get_user_meta($post_id, $field, $single);
			else
				$value = get_post_meta($post_id, $field, $single);
		}
		die($value);	
	}
	
}

X_Editable_AJAX_Callbacks::init();


function process_term($term_id, $taxonomy, $args = array()){
	$parent = 0;
		
	$use_ids = is_taxonomy_hierarchical($taxonomy);

	if ( $use_ids && isset($args['parent']) )
		$parent = $args['parent'];
	
	if ( term_exists((int)$term_id, $taxonomy, $parent) ) {
	
		if ( $use_ids )
			return (int) $term_id;
		else
			return get_term_by('id', $term_id, $taxonomy)->name;
	}

	// term does not exist => insert
	$new_term = wp_insert_term((int)$term_id, $taxonomy, $args);
	$new_term_id = $new_term['term_id'];
	
	// taxonomy is hierarchical => use IDs
	if ( $use_ids )
		return (int) $new_term_id;
	// tax not heirarchical (e.g. tags) => use names
	else
		return get_term_by('id', $new_term_id, $taxonomy)->name;
	
}

?>