<?php

class X_Editable_Admin {
	
	function __construct() {
		add_action('admin_menu', array($this, 'admin_menu'));
	}
	
	function admin_menu() {
		add_submenu_page('edit.php?post_type=acf', 'X-Editable ACF Registered Fields', 'X-Editable', X_Editable_Plugin::$EDIT_CAP, 'x-editable', array($this, 'menu_page'));
	}
	
	function menu_page() {
		// Load WP_List_Table
		if( ! class_exists( 'WP_List_Table' ) )
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		
		$fieldtable = new X_Editable_List_Table();

		echo '<div class="wrap"><h2>X-Editable ACF Registered Fields</h2>';
	
		$fieldtable->prepare_items(); 
		$fieldtable->display();
		
		echo '</div>';
	}
	
}

if ( is_admin() ) {
	
	new X_Editable_Admin;
			
	class X_Editable_List_Table extends WP_List_Table {
		
		var $data = array();
		
		function get_columns(){
			$columns = array(
				'field' 	=> 'Field',
				'classname' => 'Class Name',
				'location' => 'Location',
				'vars' 	=> 'Variables',
				'methods'   => 'Methods',
				'properties' => 'Properties',
			);
			return $columns;
		}
		
		function prepare_items() {
			
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = array(
				'field',
				'classname',
				'location',
			);
			
			$this->_column_headers = array($columns, $hidden, $sortable);
			
			//$baseSrc = file_get_contents('../fields/x-editable-acf-field.php');
			
			$baseName = 'X_Editable_ACF_Field';
			$baseClass = new ReflectionClass($baseName);
			$baseMethods = get_class_methods($baseName);
			$baseVars = get_class_vars($baseName);
			$baseProps = $baseClass->getProperties();
			$baseParent = $baseClass->getParentClass()->getName();

			$this->data[] = array(
				'field' => 'ACF base',
				'classname' => $baseName . '<br>Extends: ' . '<code>' . $baseParent . '</code>',
				'location' => '',
				'methods' => $baseMethods,
				'vars' => $baseVars,
				'properties' => array(),
			);
			
			foreach( X_Editable_Plugin::$FIELDS as $field => $location ) :
				
				$src = file_get_contents("$location");
				
				if (preg_match("/class\s+X_Editable_ACF_{$matches[1]}/i", $src)) {
					
					$class_name = 'X_Editable_ACF_' . ucfirst($field);
					$fieldclass = new ReflectionClass($class_name);
					$methods = get_class_methods($class_name);
					$vars = get_class_vars($class_name);
					$properties = $fieldclass->getProperties();
					
					$parent = $fieldclass->getParentClass()->getName();
					
					// Only show unique
					foreach($methods as $key => $meth) :
						if ( in_array($meth, $baseMethods) )
							unset($methods[$key]);
					endforeach;
					
					foreach($vars as $key => $var) :
						if ( in_array($var, $baseVars) )
							unset($vars[$key]);
					endforeach;
					
					foreach($properties as $key => $prop) :
						if ( in_array($prop, $baseProps) )
							unset($properties[$key]);
					endforeach;
					
					// Location
					if ( strstr($location, "wp-content\plugins") ) {
						$loc = 'Plugin';
							
						if ( strstr($location, 'x-editable') ) {
							$loc .= ': X-Editable';
						}
					}
					elseif ( strstr($location, get_stylesheet_directory()) ){
						$loc = 'Theme';
					}
					else{
						$loc = $location;
					}
				}
				
				$this->data[] = array(
					'field' => $field,
					'classname' => $fieldclass->name . '<br>Extends: ' . '<code>' . $parent . '</code>',
					'location' => $loc,
					'methods' => $methods,
					'vars' => $vars,
					'properties' => $properties,
				);
				
			endforeach;
			
			$this->items = $this->data;
		}
	
		function column_default( $item, $column_name ) {
			
			switch( $column_name ) { 
				
				case 'field':
					echo '<strong>' . $item[$column_name] . '</strong>';
					break;
				case 'classname':
				case 'location':
					echo $item[$column_name];
					break;
					
				case 'methods':
				case 'properties':
					if ( isset($item[$column_name]) && is_array($item[$column_name]) ) {
						echo '<code>' . implode('</code><br><code>', $item[$column_name]) . '</code>';
					}
					else
						echo $item[$column_name];
					break;
				
				case 'vars':
					foreach( $item['vars'] as $k => $v ) :
						echo '<code>' . $k . ' = ' . $v . '</code><br>';
					endforeach;
					break;
				
				default:
					break;
			}
		
		}
		
	}
	
}



?>