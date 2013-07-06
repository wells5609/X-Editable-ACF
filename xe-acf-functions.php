<?php

class XE_ACF_Functions {
	
	
	public static function init() {
		
		add_action( 'xe/register_field', array('XE_ACF_Functions', 'register_field'), 10, 2 );
		
		if ( ! has_action('xe/create_external', array('XE_ACF_Functions', 'create_external')) ) {
			
			add_action( 'xe/create_external', array('XE_ACF_Functions', 'create_external'), 5, 3);
		
		}
		
		add_action( 'xe/create_element', array('XE_ACF_Functions', 'create_element'), 5, 4);
		
		add_action( 'xe/create_label', array('XE_ACF_Functions', 'create_label'), 5, 1);
		
	}
		
	
	public static function create_external( $field, $html, $as_ul ) {
		
		$html_tag = apply_filters( 'xe/external/wrapper/html/tag', 'div', $field );
		$html_id = $field['name'] . '-content';	// do not change or filter - JS relies on format
		$html_class = apply_filters( 'xe/external/wrapper/html/css_class', array('xe-values'), $field );
		
		// text formatted for external display
		if ( $field['return_format'] ) {
			// expecting filter for this format
			$text = apply_filters('xe/external/text/type='. $field['type'] . '/format=' . $field['return_format'], $field['value'], $field);
		}
		else {
			$text = apply_filters('xe/external/text/type='. $field['type'], $field['value'], $field);
		}
		
		do_action('xe/external/before', $field);
		
		// wrapper
		$echo = '<'.$html_tag.' id="'.$html_id.'" class="'. implode( ' ', $html_class ) .'">';
		
		// values as unordered list
		if ( $as_ul && is_array($text) ) {
			
			$ul_class = apply_filters( 'xe/external/ul/html/css_class', array('xe-ul'), $field );
			
			do_action('xe/external/ul/before', $field);
			
			$echo .= '<ul class="' . implode( ' ', $ul_class ) . '">';
			
			// multiple values - text returned as array
			foreach($text as $t) :
				$echo .= '<li>' . $t . '</li>';
			endforeach;
						
			$echo .= '</ul>';
			
			do_action('xe/external/ul/after', $field);
			
		}
		
		// no UL
		else {
			
			// multiple values, no UL
			if ( is_array($text) ) {
				
				$textArray = array();
				
				foreach($text as $t) :
					$textArray[] = $t;
				endforeach;
				
				$echo .= implode(', ', $textArray);
				
			}
			// just 1 value - returned as string
			elseif ( $text ) {
				$echo .=  $text;	
			}
						
		}
		
		$echo .= '</'.$html_tag.'>';
		
		do_action('xe/external/after', $field);
		
		echo $echo;
			
	}
	
	
	public static function create_label( $field ) {
		
		if ( $field['label'] ) {
			
			$html_tag	= apply_filters( 'xe/label/html/tag', 'span', $field );
			$html_class = apply_filters( 'xe/label/html/css_class', array('xe-label'), $field );
			$text		= apply_filters( 'xe/label/html/text', $field['label'], $field );
			
			do_action('xe/label/before', $field);
			
			echo '<'.$html_tag.' class="x-editable ' . implode( ' ', $html_class ) .'">' . $text . ' </'.$html_tag.'>';
		
			do_action('xe/label/after', $field);
		}
		
	}
		
	public static function create_element( $field, $object_id, $html, $options ) {
		
		$echo = '';
				
		do_action( 'xe/element/before', $field );
				
		$echo .= '<' . $html['tag'] . ' ';
		
		// element ID - format: xe-{{meta_name or taxonomy}}
		$echo .= 'id="xe-' . $field['name'] . '" ';
		
		// CLASS
		if ( isset($html['css_class']) ) {
			$echo .= 'class="' . implode(' ', $html['css_class']) . '" ';
		}
		
		// NAME - IMPORTANT - submitted to server
		$echo .= 'data-name="'. $field['name'] .'" ';
		
		// NONCE
		$echo .= 'data-nonce="'. wp_create_nonce( $field['name'] ) .'" ';
		
		// INPUT TYPE
		$echo .= 'data-type="'. $options['input_type'] .'" ';
		
		// Pop-up title	
		$echo .= 'data-original-title="Edit: '. $field['label'] .'" ';
		
		// ACF field type
		$echo .= 'data-acf_type="' . $field['type'] . '" ';
		
		//	VALUE
		$echo .= 'data-value="' . $html['value'] . '" ';
				
		//	PK (primary key)
		$echo .= 'data-pk="'. $object_id .'" ';
		
		// SOURCE
		if ( isset($html['source']) ) {
			$echo .= 'data-source="' . esc_attr( $html['source'] ) . '" ';	
		}
		
		//	user-defined data-* options
		if ( isset($html['data_args']) ) {
			foreach ($html['data_args'] as $data_option => $data_value) :
				$echo .= 'data-' . $data_option . '="' . esc_attr($data_value) . '" ';
			endforeach;
		}

		$echo .= '>';
		
		//	TEXT
		
		// if display values externally, change edit text to (default) "Edit" (fields can filter)
		if ( isset($html['data_args']) && in_array('external', $html['data_args']) ) {
			$echoText = apply_filters('xe/element/html/text/external/type=' . $field['type'], 'Edit <br>', $field);
		}
		else {
			$echoText = $html['text'];
		}
		$echo .= $echoText;
				
		// close tag	
		$echo .= '</' . $html['tag'] . '>';
		
		echo $echo;
		
		do_action( 'xe/element/after', $field );
				
	}
	
	
	function format_array( $array, $before_each = '', $after_each = '' ) {
		
		if ( is_array($array) ) {
			
			$return = array();
			
			foreach($array as $a ) :
				
				$return[] = $before_each . $a . $after_each;
				
			endforeach;
		
			return $return;
						
		}
		else 
			return $array;
		
	}
	
	
}

XE_ACF_Functions::init();

?>