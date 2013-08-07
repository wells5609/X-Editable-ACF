<?php 
/**	X_Editable_Field_Functions
 *
 *	This class provides the callback functions for create_external, create_element, and create_label actions.
 *	Much of this class is ACF-specific, but all X-Editables are routed through here regardless to produce the HTML.
 *
 */

class X_Editable_Field_Functions {
	
	public static function init() {
		
		add_action( 'xe/create_external', array(__CLASS__, 'create_external'), 5, 4);
			
		add_action( 'xe/create_element', array(__CLASS__, 'create_element'), 5, 4);
		
		add_action( 'xe/create_label', array(__CLASS__, 'create_label'), 5, 2);
		
	}
		
	/**	create_external
	 *
	 *	A poorly named function (is create_values better?)
	 *	This produces the "external values" output HTML - e.g. a <ul> of taxonomy terms.
	 *	Basically, it shows the field values outside the editable HTML element (usually <a>)
	 *
	 */
		public static function create_external( $id, $field, $html, $as_ul ) {
			
			$echo = '';
			
			$ext_html = array(
				'tag' => 'div',
				'attributes' => array(
					'class' => array('x-editable', 'xe-values'),
				),
			);
			$ext_html = apply_filters('xe/external/wrapper/html', $ext_html, $field);
			
			$html_id = $field['name'] . '-' . $id . '-content';	// do not change/filter - JS relies on format
			
			// text formatted for external display
			if ( $field['return_format'] ) {
				
				// expecting filter for this format (from field class)
				$text = apply_filters('xe/external/text/type='. $field['type'] . '/format=' . $field['return_format'], $field['value'], $field);
			}
			else {
				$text = apply_filters('xe/external/text/type='. $field['type'], $field['value'], $field);
			}
			
			do_action('xe/external/before', $field);
			
			// wrapper
			$echo .= '<'.$ext_html['tag'].' id="'.$html_id.'" ';
			
			foreach($ext_html['attributes'] as $attr => $val) :
				if ( isset($val) && is_array($val) )
					$val = implode(' ', $val);
				$echo .= $attr . '="' . $val . '" ';
			endforeach;
			$echo .= '>';
			
			// values as unordered list
			if ( $as_ul && is_array($text) ) {
				
				$ul_html = apply_filters( 'xe/external/ul/html/attributes', array( 'class' => array('x-editable', 'xe-ul') ), $field );
				
				do_action('xe/external/ul/before', $field);
				
				$echo .= '<ul ';
					
				foreach($ul_html as $attr => $val) :
					if ( isset($val) && is_array($val) )
						$val = implode(' ', $val);
					$echo .= $attr . '="' . $val . '" ';
				endforeach;
				
				$echo .= '>';
				
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
				
				elseif ( $text )
					$echo .=  $text;		
			}
			
			$echo .= '</'.$ext_html['tag'].'>';
			
			do_action('xe/external/after', $field);
			
			echo $echo;
		}
	
	
	/**	create_label
	 *
	 *	Creates a label to be shown before the element.
	 *
	 */
		public static function create_label( $field, array $attributes = NULL ) {
			
			if ( $field['label'] ) {
				
				$defaults = array(
					'tag' => 'span',
					'attributes' => array(
						'class' => array('x-editable', 'xe-label'),
					),
					'text' => $field['label'],
				);
				
				$html = apply_filters('xe/label/html', $defaults, $field);
				
				if ( NULL !== $attributes ) {
					$html['attributes'] = array_merge($html['attributes'], $attributes);	
				}
				
				do_action('xe/label/before', $field);
				
				echo '<'.$html['tag'].' ';
					
				foreach($html['attributes'] as $attr => $val) :
					if ( isset($val) && is_array($val) )
						$val = implode(' ', $val);
					echo $attr . '="' . $val . '" ';
				endforeach;
				
				echo '>' . $html['text'] . '</'.$html['tag'].'>';
			
				do_action('xe/label/after', $field);
			}
		}
	
	
	/**	create_element
	 *
	 *	This is where all the HTML magic happens.
	 *	Well, most of it
	 *
	 */ 
		public static function create_element( $field, $object_id, $html, $options ) {
			
			$echo = '';
					
			do_action( 'xe/element/before', $field );
					
			$echo .= '<' . $html['tag'] . ' ';
			
			// element ID - format: xe-{{meta_name or taxonomy}}
			$echo .= 'id="xe-' . $field['name'] . '" ';
			
			// CLASS
			if ( isset($html['css_class']) )
				$echo .= 'class="' . implode(' ', $html['css_class']) . '" ';
			
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
			
			// KEY
			if ( $field['key'] ) {
				$key = trim(str_replace('field_', '', $field['key']));
				$echo .= 'data-key="' . $key . '" ';
			}
			
			//	VALUE
			$echo .= 'data-value="' . $html['value'] . '" ';
					
			//	PK (primary key)
			$echo .= 'data-pk="'. $object_id .'" ';
			
			// SOURCE
			if ( isset($html['source']) )
				$echo .= 'data-source="' . esc_attr( $html['source'] ) . '" ';
			
			//	user-defined data-* options
			if ( isset($html['data_args']) ) {
				foreach ($html['data_args'] as $data_option => $data_value) :
					$echo .= 'data-' . $data_option . '="' . esc_attr($data_value) . '" ';
				endforeach;
			}
	
			$echo .= '>';
			
			//	TEXT
			
			// if display values externally, change edit text to (default) "Edit" (fields can filter)
			if ( isset($html['data_args']) && in_array('edit_link', $html['data_args']) ) {
				
				if ( XE_CAN_EDIT )
					$echoText = apply_filters('xe/element/html/text/external/type=' . $field['type'], 'Edit <br>', $field);
				else
					$echoText = apply_filters('xe/element/html/text/external/type=' . $field['type'], '', $field);
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
	
}

X_Editable_Field_Functions::init();

?>