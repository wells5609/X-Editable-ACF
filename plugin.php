<?php
/*
Plugin Name: X-Editable ACF
Plugin URI: https://github.com/wells5609/X-Editable-ACF
Description: Edit Advanced Custom Fields from the front-end using X-Editable. Also has class for working with native meta (no template tags).
Version: 0.3.3
Author: Wells Peterson
License: GPL
Copyright: Wells Peterson
*/

/**
* Setup the X-Editable-ACF plugin
*
* Use add_theme_support( 'x-editable-acf' ); in theme functions.php to enable.
*/

add_action( 'init', 'x_editable_acf' );

function x_editable_acf() {
	
	if ( ! current_theme_supports('x-editable-acf') && ! current_theme_supports('x-editable') ) {
		return false;
	}
	
	// Define capability required to edit - default 'edit_posts'
	$capability = apply_filters('xeditable/user_cap_to_edit', 'edit_posts');
	
	if ( current_user_can($capability) ) {
		define('XE_CAN_EDIT', true);
	}
	else {
		define('XE_CAN_EDIT', false);	
	}
	
	include_once 'fields/_base.php';
	
	require_once 'fields/_base-acf.php';
	
	require_once 'inc/class.xe-acf-functions.php';
	
	require_once 'inc/template-tag.php';
	
	new XE_ACF_Plugin();
		
}

// Plugin class
class XE_ACF_Plugin {
	
	public $user_fields = array();

	private 
		$version = '0.3.2',
		$xeditable_version = '1.4.5';
	
	function __construct() {		
		
		$this->hooks();
		
		$this->register_scripts();
				
		$this->default_fields();
		
		$this->user_fields();
		
	}
		
	private function hooks() {
		
		// Meta edit 
		add_action( 'wp_ajax_xeditable_meta_handler', array($this, 'meta_handler') );
		add_action( 'wp_ajax_nopriv_xeditable_meta_handler', array($this, 'must_log_in') );
		
		// Meta load
		add_action( 'wp_ajax_xeditable_meta_load', array($this, 'load_meta') );
		add_action( 'wp_ajax_nopriv_xeditable_meta_load', array($this, 'must_log_in') );
		
		// Gets user options (for meta)
		add_action( 'wp_ajax_xeditable_user_options', array($this, 'user_options') );
		add_action( 'wp_ajax_nopriv_xeditable_user_options', array($this, 'must_log_in') );
		
		// Gets taxonomy options (terms)
		add_action( 'wp_ajax_xeditable_tax_options', array($this, 'tax_options') );
		add_action( 'wp_ajax_nopriv_xeditable_tax_options', array($this, 'must_log_in') );
		
		// Taxonomy (ACF) field
		add_action( 'wp_ajax_xeditable_acf_taxonomy', array($this, 'tax_handler') );
		add_action( 'wp_ajax_nopriv_xeditable_acf_taxonomy', array($this, 'must_log_in') );
		
		// DEPR -- Taxonomy edit
		add_action( 'wp_ajax_xeditable_tax_handler', array($this, 'tax_handler') );
		add_action( 'wp_ajax_nopriv_xeditable_tax_handler', array($this, 'must_log_in') );
		
		// Term load
		add_action( 'wp_ajax_xeditable_term_load', array($this, 'load_terms') );
		add_action( 'wp_ajax_nopriv_xeditable_term_load', array($this, 'must_log_in') );
			
	}
	
	private function register_scripts() {
		
		// Bootstrap editable css
		wp_register_style('x-editable', plugins_url( 'assets/bootstrap-editable.min.css' , __FILE__ ), array('bootstrap'), $this->xeditable_version );
		// Bootstrap editable js
		wp_register_script('x-editable', plugins_url( 'assets/bootstrap-editable.min.js' , __FILE__ ), array('jquery', 'bootstrap-js'), $this->xeditable_version, true );
		// X-Editable WP
		wp_register_script('x-editable-acf', plugins_url('assets/x-editable-acf.js', __FILE__ ), array('jquery', 'x-editable'), $this->version, true );
		
	}
	
	public function enqueue_scripts() {
		wp_enqueue_style('x-editable');
		wp_enqueue_script('x-editable-acf');
		wp_localize_script(	'x-editable-acf', 'xeditable', array( 'ajaxurl' => admin_url('admin-ajax.php') ) );	
	}
	
	
	private function default_fields() {
		
		$default_fields = array(
			'number',
			'textarea',
			'select',
			'taxonomy',
			'date',
			'true_false',
			'user',
		);
		
		foreach($default_fields as $field) :
			
			include_once 'fields/' . $field . '.field.php';
			
		endforeach;
		
	}
	
	private function user_fields() {
		
		// Default path is 'fields' directory of theme
		$default_path = get_stylesheet_directory() . '/fields/';
		
		$this->user_fields = apply_filters('xe/user_fields', $this->user_fields);
		
		foreach($this->user_fields as $field) :
			
			// default filename is {$fieldname}.field.php
			$default_location = $default_path . $field .'.field.php';
			
			$file_location = apply_filters('xe_' . $field . '_field_path', $default_location);
						
			include_once $file_location;
			
		endforeach;	
		
	}
	
	
	/* ===================
		AJAX HANDLERS
	=================== */
	
	// user not logged in
	public function must_log_in() {
		echo'You must log in to use this feature.';	
	}
	
	// Taxonomy terms (add/remove to/from an object)
	public function tax_handler() {
		
		$object_id = (int) $_POST['pk']; // the POST ID.
		$name = trim($_POST['name']); // the field name - used for nonce
		$taxonomy = trim($_POST['tax']); // will be the taxonomy name
		$value = wp_kses_stripslashes( $_POST['value'] );
		$single = $_POST['issingle'];
	
		if ( ! wp_verify_nonce( $_POST['nonce'], $name )) {
			header('HTTP 400 Bad Request', true, 400);
			exit('Wise guy, huh?');
		}
		
		if ($value) {
			
			$terms = array();
			
			if ( is_string($value) ) {
				
				// we have term name
				if ( ! is_numeric($value) ) {
					$exists = get_term_by('name', $value, $taxonomy);
				}
				// we have term iD
				else {
					$exists = get_term_by('id', $value, $taxonomy);	
				}
				
				if ( ! $exists ) {
					// add the term
					$term = wp_insert_term($value, $taxonomy);
					$term_id = $term['term_id'];
				}
				else {
					// probably an ID
					if ( is_numeric($value) ) {
						$term_id = $value;	
					}
					else {
						$term_id = $exists->term_id;
					}
					
				}
				
				$terms[] = (int) $term_id;	
			}
			
			elseif ( is_array($value) ) {
				
				foreach($value as $val) :
					$terms[] = (int) $val;	
				endforeach;
				
			}
			
			elseif ( $single ) {
				$terms = (int) $value;	
			}
			
			else {
				$terms[] = (int) $value;
			}
			
								
			wp_set_object_terms($object_id, $terms, $taxonomy, $single);
	
		}
		elseif ( $object_id && $taxonomy && ! isset($value) ) {
			wp_set_object_terms($object_id, array(), $taxonomy, $single);	
		}
		
		die();	
		
	}
	
	// Handles HTTP GET requests for tax terms (used to populate dropdowns/checkboxes).
	public function tax_options() {
		
		$tax = trim($_REQUEST['tax']); 
		$string = $_REQUEST['string'];
		$hide_empty = $_REQUEST['hide_empty'];
		
		if ( $hide_empty ) {
			$getTermArgs = 'hide_empty=1';	
		}
		else {
			$getTermArgs = 'hide_empty=0';		
		}
		
		if ( ! is_null($tax) ) {
			
			$terms = get_terms($tax, $getTermArgs );
			
			if ( $terms ) {
				
				$options = array();
				
				foreach ($terms as $term) :
					
					// terms as JS object
					if ( ! $string ) {
						$options[$term->term_id] = $term->name;
					}
					// terms as string (for typeahead)
					else {
						$options[] = $term->name;	
					}
					
				endforeach;
				
				echo json_encode($options);
	
			}
			
		}
		
		die();
	}
	
	// user options
	public function user_options() {
		
		$role = trim($_REQUEST['role']); 
		
		if ( ! is_null($role) ) {
			
			$users = get_users( 'role=' . $role );
			
			if ( $users ) {
				
				$options = array();
				
				foreach ($users as $user) :
					
					$options[$user->ID] = $user->display_name;
					
				endforeach;
				
				echo json_encode($options);
	
			}
			
		}
		
		die();
	}
	
	// Load terms
	public function load_terms() {
		
		$tax = $_REQUEST['tax'];
		$object_id = $_REQUEST['object_id'];
		$as_ul = $_REQUEST['as_ul'];
		
		$terms = get_the_terms($object_id, $tax);
				
		if ($terms) {
			
			if ( ! $as_ul ) {
				
				$term_names = array();
				foreach($terms as $term) :
					$term_names[] = $term->name;
				endforeach;
				
				echo implode(', ', $term_names);
				
			}
			else {
				echo '<ul class="x-editable ajax-terms">';
				foreach($terms as $term) :
					echo '<li>' . $term->name . '</li>';
				endforeach;
				echo '</ul>';
			}
			
		}
				
		die();
			
	}
	
	
	// load meta handler (called on edit success)
	public function load_meta() {
		
		$post_id = $_REQUEST['post_id'];
		$field = trim($_REQUEST['field']);
		$object_name = trim($_REQUEST['object_name']);
		$single = $_REQUEST['single'];
		
		$value = false;
		
		if (function_exists('get_field')) {
			
			// Prefix object_id with object type (taxonomy name, etc.)
			if ( $object_name ) {
				$value = get_field($field, $object_name . '_' . $post_id);
			}
			else {
				$value = get_field($field, $post_id);	
			}
			
		}
		
		if ( $value ) {
			echo $value;	
		}
		else {
			if ( 'user' === $object_name ) {
				$val = get_user_meta($post_id, $field, $single);
			}
			else {
				$val = get_post_meta($post_id, $field, $single);
			}
			
			echo $val;
			
		}
		
		die();
			
	}

	// Meta AJAX handler function
	public function meta_handler() {
	
		$object_id = $_POST['pk']; // post id.
		$name = trim($_POST['name']); // the ACF field key
		$value = wp_kses_stripslashes($_POST['value']); // uses "data-value" if present, otherwise html contents.
		$acf_type = $_POST['acf_type'];
		$object_name = $_POST['object_name']; // if we're editing Term, User, etc. (not set for Post)
			   
		// nonce must match name.
		if ( ! wp_verify_nonce( $_POST['nonce'], $name )) {
			header('HTTP 400 Bad Request', true, 400);
			exit('Wise guy, huh?');
		}
		
		// If value is not blank, update post meta
		// Using !is_null() allows us to post '0' or 'false' as the value.
		if ( ! is_null($value) ) {
						
			if ( function_exists('update_field') && ! empty($acf_type) ) {
				
				// Prefix object_id with object type (taxonomy name, etc.)
				if ( $object_name ) {
					
					$object_id = $object_name . '_' . $object_id;
				}
				
				if ( 'user' == $acf_type ) {
					
					update_field( $name, array($value), $object_id );
				}
				else {
					update_field( $name, $value, $object_id );
				}
			
			}
			else {
				
				if ( 'user' === $object_name ) {
					update_user_meta($object_id, $name, $value);
				}
				else {
					update_post_meta($object_id, $name, $value);
				}
				
			}
			
			print_r($_POST); // debug response
		
		}
		else {
			header('HTTP 400 Bad Request', true, 400);
			exit("I think you broke it");
		}
		
		die();	
	}
	
}
?>