<?php
/*
Plugin Name: X-Editable ACF
Plugin URI: 
Description: Edit ACF from the front end using X-Editable.
Version: 0.2.1
Author: Wells Peterson
Author URI: http://wellspeterson.com/
License: GPL
Copyright: Wells Peterson
*/

/**
* Setup the WP X-Editable plugin
*
* Use add_theme_support( 'x-editable' ); in theme functions.php to enable.
*/

add_action( 'init', 'x_editable_acf' );

function x_editable_acf() {
	
	if ( ! current_theme_supports( 'x-editable-acf' ) ) {
		return false;
	}
	
	new XE_ACF_Plugin();
	
	require_once 'x-editable-acf.php';
	require_once 'x-editable-acf-functions.php';
	
}

// Plugin class
class XE_ACF_Plugin {

	private 
		$version,
		$xeditable_version;
		
	function __construct() {		
		
		$this->version = '0.2.1';
		$this->xeditable_version = '1.4.4';
		
		$this->register();
		$this->hooks();
		$this->enqueue();
	}
	
	private function register() {
		
		// Bootstrap editable css
		wp_register_style('x-editable', plugins_url( 'assets/bootstrap-editable.min.css' , __FILE__ ), array('bootstrap'), $this->xeditable_version );
		// Bootstrap editable js
		wp_register_script('x-editable', plugins_url( 'assets/bootstrap-editable.min.js' , __FILE__ ), array('jquery', 'bootstrap-js'), $this->xeditable_version, true );
		// X-Editable WP
		wp_register_script('x-editable-wp', plugins_url('assets/x-editable-wp.js', __FILE__ ), array('jquery', 'x-editable'), $this->version, true );
		
	}
	
	private function hooks() {
		
		// Meta edit 
		add_action( 'wp_ajax_xeditable_meta_handler', array($this, 'meta_handler') );
		add_action( 'wp_ajax_nopriv_xeditable_meta_handler', array($this, 'must_log_in') );
		
		// Meta load
		add_action( 'wp_ajax_xeditable_meta_load', array($this, 'load_meta') );
		add_action( 'wp_ajax_nopriv_xeditable_meta_load', array($this, 'load_meta') );
		
		// Gets user options (for meta)
		add_action( 'wp_ajax_xeditable_user_options', array($this, 'user_options') );
		add_action( 'wp_ajax_nopriv_xeditable_user_options', array($this, 'must_log_in') );
		
		// Gets taxonomy options (terms)
		add_action( 'wp_ajax_xeditable_tax_options', array($this, 'tax_options') );
		add_action( 'wp_ajax_nopriv_xeditable_tax_options', array($this, 'must_log_in') );
		
		// NEW! Taxonomy (ACF) field
		add_action( 'wp_ajax_xeditable_acf_taxonomy', array($this, 'tax_handler') );
		add_action( 'wp_ajax_nopriv_xeditable_acf_taxonomy', array($this, 'must_log_in') );
		
		// DEPR -- Taxonomy edit
		add_action( 'wp_ajax_xeditable_tax_handler', array($this, 'tax_handler') );
		add_action( 'wp_ajax_nopriv_xeditable_tax_handler', array($this, 'must_log_in') );
		
		// Term load
		add_action( 'wp_ajax_xeditable_term_load', array($this, 'load_terms') );
		add_action( 'wp_ajax_nopriv_xeditable_term_load', array($this, 'must_log_in') );
			
	}
	
	private function enqueue() {
		wp_enqueue_style('x-editable');
		wp_enqueue_script('x-editable-wp');
		wp_localize_script(	'x-editable-wp', 'xeditable', array( 'ajaxurl' => admin_url('admin-ajax.php') ) );	
	}
	
	
	/* ===================
		AJAX HANDLERS
	=================== */
	
	// user not logged in
	public function must_log_in() {
		echo 'You must log in to use this feature.';	
	}
	
	// Taxonomy terms (add/remove to/from an object)
	public function tax_handler() {
		
		$object_id = (int) $_POST['pk']; // the POST ID.
		$name = $_POST['name']; // the acf-formatted field name - used for nonce.
		$taxonomy = trim($_POST['tax']); // will be the taxonomy name
		$value = $_POST['value'];
		$single = $_POST['issingle'];
	

		// nonce must match name.
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
				
				if ( false === $exists ) {
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
		
		die();	
		
	}
	
	// Handles HTTP GET requests for tax terms (used to populate dropdowns/checkboxes).
	public function tax_options() {
		
		$tax = trim($_REQUEST['tax']); 
		$string = $_REQUEST['string'];
		
		if ( ! is_null($tax) ) {
			
			$terms = get_terms($tax, array('hide_empty=0') );
			
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
		$field = $_REQUEST['field'];
		$object_name = $_REQUEST['object_name'];
		
		if (function_exists('get_field')) {
			// Prefix object_id with object type (taxonomy name, etc.)
			if ( $object_name ) {
				$post_id = $object_name . '_' . $post_id;
			}
			$value = get_field($field, $post_id);	
		}
		else {
			$value = get_post_meta($post_id, $field);
		}
		
		echo $value;
		
		die();
			
	}

	// Meta AJAX handler function
	public function meta_handler() {
	
		$object_id = $_POST['pk']; // post id.
		$name = trim($_POST['name']); // the ACF field key
		$value = $_POST['value']; // uses "data-value" if present, otherwise html contents.
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
						
			if (function_exists('update_field')) {
				
				// Prefix object_id with object type (taxonomy name, etc.)
				if ( $object_name ) {
					
					$object_id = $object_name . '_' . $object_id;	
				
				}
				
				if ( is_array($value) ) {
					
					$array_vals = array();
					
					foreach($value as $val) :
						$array_vals[] = $val;
					endforeach;
					
					update_field($name, $array_vals, $object_id);
				
				}
				elseif ( 'user' == $acf_type ) {
					
					update_field( $name, array($value), $object_id );	
				
				}
				else {
					update_field( $name, $value, $object_id );
				}
			
			}
			else {
				
				if ( is_array($value) ) {
					
					foreach($value as $val) :
						update_post_meta($object_id, $name, $val);
					endforeach;	
				
				}
				else {
					// THIS WILL ONLY WORK FOR POST OBJECTS !!
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

	
} // delete this and puppies get neglected.


?>