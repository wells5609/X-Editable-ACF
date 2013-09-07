<?php

class X_Editable_Admin {
	
	function __construct() {
		add_action('admin_menu', array($this, 'admin_menu'));
	}
	
	function admin_menu() {
		$page = add_submenu_page('edit.php?post_type=acf', 'X-Editable ACF Registered Fields', 'X-Editable', X_Editable_Plugin::$EDIT_CAP, 'x-editable', array($this, 'menu_page'));
		add_action('admin_head-' . $page, array(__CLASS__, '_admin_style'));
	}
	
	function _admin_style(){
	?>	
<style type="text/css">
.pull-left, .pull-right {display:block}
ul.pull-left, ul.pull-right {display:inline-block}
.pull-left {float:left}
.pull-right {float:right}
.pull-left.col {margin-right:20px}
.pull-right.col {margin-left:20px}
.widefat td .ul {margin-top:5px; margin-bottom:5px;}
</style>
	<?php
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
				'location' 	=> 'Location',
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
			
			// Base class
			$baseName = 'X_Editable_Meta';
			$baseClass = new ReflectionClass($baseName);
			$baseMethods = get_class_methods($baseName);
			$baseProps = $baseClass->getProperties();

			$this->data[] = array(
				'field' => 'Base',
				'classname' => $baseName,
				'location' => 'Plugin: X-Editable',
				'methods' => $baseMethods,
				'vars' => $baseVars,
				'properties' => $baseProps,
			);
			
			// ACF Base class
			$acfBaseName = 'X_Editable_ACF_Field';
			$acfBaseClass = new ReflectionClass($acfBaseName);
			$acfBaseMethods = array_diff(get_class_methods($acfBaseName), $baseMethods);
			$acfBaseProps = array_diff($acfBaseClass->getProperties(), $baseProps);
			$acfBaseParent = $acfBaseClass->getParentClass()->getName();

			$this->data[] = array(
				'field' => 'ACF base',
				'classname' => $acfBaseName . '<br>Extends: <code>' . $acfBaseParent . '</code>',
				'location' => 'Plugin: X-Editable',
				'methods' => $acfBaseMethods,
				'properties' => $acfBaseProps,
			);
			
			// setup arrays to filter out parent(s)'s methods and properties from child classes
			$parentMethods = array_merge($baseMethods, $acfBaseMethods);
			$parentProps = array_merge($baseProps, $acfBaseProps);
			
			// ACF Field classes
			foreach( X_Editable_Plugin::$FIELDS as $field => $location ) :
				
				$src = @file_get_contents("$location");
				
				// find the class name
				if (preg_match("/class\s+X_Editable_ACF_{$matches[1]}/i", $src)) {
					
					$class_name = 'X_Editable_ACF_' . $field;
					$fieldclass = new ReflectionClass($class_name);
					$methods = array_diff(get_class_methods($class_name), $parentMethods);
					$properties = array_diff($fieldclass->getProperties(), $parentProps);
					$parent = $fieldclass->getParentClass()->getName();
			
					// Make location more human-friendly
					if ( strstr($location, "plugins") ) {
						$loc = 'Plugin';
						if ( strstr($location, 'x-editable') )
							$loc .= ': X-Editable';
					}
					elseif ( strstr($location, get_template_directory()) )
						$loc = 'Theme';
					elseif ( strstr($location, get_stylesheet_directory()) )
						$loc = 'Theme (Child)';
					else
						$loc = $location;
				}
				
				// child class data
				$this->data[] = array(
					'field' => $field,
					'classname' => $fieldclass->name . '<br>Extends: ' . '<code>' . $parent . '</code>',
					'location' => $loc,
					'methods' => $methods,
					'properties' => $properties,
				);
				
			endforeach;
			
			// setup items with data
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
						$num = count($item[$column_name]);
						if ( $num > 10 ) {
							$perCol = floor($num/2);
							echo '<ul class="pull-left col">';
							for($i=0; $i < $perCol; $i++){
								echo '<li><code>' . $item[$column_name][$i] . '</code></li>';
							}
							echo '</ul><ul class="pull-left col">';
							for($i=$perCol; $i <= $num; $i++){
								echo '<li><code>' . $item[$column_name][$i] . '</code></li>';
							}
							echo '</ul>';
						}
						else {
							echo '<ul>';
							foreach($item[$column_name] as $it):
								echo '<li><code>' . $it . '</code></li>';
							endforeach;
							echo '</ul>';
						}
					}
					else
						echo $item[$column_name];
					break;
				
				default:
					break;
			}
		
		}
		
	}
	
}



?>