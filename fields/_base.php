<?php

class X_Editable_Meta {
		
	public
	
	/** id
	 * 
	 *	@var integer
	 * 
	 *	Queried object ID
	 */ 
		 $id,


	/** meta	
	 * 
	 * 	@var array
	 * 
	 *	single		(boolean)	whether value is single
 	 *	value		(mixed)		meta_value from db
	 *	etc...
	 */
		$meta = array(
			'single' => false,
			//'name' => ...
		),		
		
		
	/**	html
	 * 
	 * @var	array
	 *	
	 * 	tag			(string)	HTML tag to use (default 'a')
	 *	text		(string)	text to display
	 *	value		(string)	value for data-value
	 *	css_class	(array)i	CSS classes for element
	 *	data_args	(array)a	data-* attributes
	 */
	 
		$html = array(
			'tag'	=> 'a',
			'css_class'	=> array(),
			'data_args'	=> array(),
		),
	
					
	/** options
	 * 
	 * 	@var array
	 * 
	 * 	show_label		(boolean)	show a label before the element
	 * 	show_external	(boolean)	show the field's values externally (ie outside the editable element)
	 *	input_type		(string)	input type to use
	 *	object			(string)	object to edit (post, user, term)
	 *		...
	 */	
		$options = array(
			'show_label' => true,
			'show_external' => false,
			'input_type' => 'text',
			'object' => 'post',
		),
		
		
	/** caps
	 *	
	 *	@var array
	 *	
	 *	view		(string)
	 *	edit		(string)
	 */
		$caps = array();
		
	
	/**	_is_setup
	 *	
	 * 	Whether the output variables are setup yet
	 * 	
	 * 	@var boolean
	 * 
	 */
		private $_is_setup = false;
	
	
		// constructor
		public function __construct( $id, $meta_key, $args = array() ) {
			
			/** Checks if user is able to edit (add_action with priority 1)
			*	If so, scripts are enqueued, otherwise element set to 'span' (default: 'a')
			*/
			do_action('xe/before_construct', $this, $meta_key, $id, $args);			
			
			
			$this->id = (int) $id;
						
			// args
			if ( isset($args['object']) && $args['object'] ) {
				$this->setOption('object', $args['object']);	
			}
			
			if ( isset($args['single']) && $args['single'] ) {
				$this->setMeta('single', $args['single']);
				unset($args['single']); // unset before merged with Options
			}
			
			// Options - add unknown args
			$this->options = apply_filters('xe/construct/options', 
				array_merge($this->options, $args), 
				$meta_key
			);
			
			
			// Using ACF => Bypass rest of setup
			if ( true === $args['_acf'] ) {
				$this->acf_setup($this->id, $meta_key, $this->options);
			}
			// Not using ACF => make best guess for default value, text, label
			else {
			
				$this->setMeta('key', $meta_key);
				$this->setMeta('name', $meta_key);
				
				// Post or user meta?
				if ( 'post' === $this->getOption('object') ) {
				
					$this->setMeta('value', get_post_meta($id, $this->getMeta('key'), $this->getMeta('single')));
				}
				elseif ( 'user' === $this->getOption('object') ) {
					
					$this->setMeta('value', get_user_meta($id, $this->getMeta('key'), $this->getMeta('single')));
				}
				// Take first element from array value if single
				if ( $this->getMeta('single') && is_array($this->getMeta('value')) ) {
					
					$this->setMeta('value', $this->meta['value'][0]);
				}
				
				// hackish str_replace/see-saw to get a decent label from meta_key
				if ( ! $this->hasOption('label') && ! $this->hasMeta('label') ) {
					
					$this->setMeta('label', ucwords( implode(' ', explode( '_', str_replace('-', ' ', $meta_key) )) ));
				}
			}
			
			
			// Default capability to edit: 'edit_posts' (takes precedent over plugin-wide edit capability)
			$this->setCap('edit', apply_filters('xe/caps/edit/name=' . $meta_key, 
				'edit_posts', 
				$this->meta
			));
			
			// No default view capability (i.e. anyone can view)
			$this->setCap('view', apply_filters('xe/caps/view/name='. $meta_key, 
				false, 
				$this->meta
			));
			
			do_action('xe/after_construct', $this);
			
		}
		
	
	/* =============
		MAGIC
	============= */
					
			
		/** __get
		 *
		 *	Gets a variable value.
		 *
		 */
			public function __get($variable) {
				return $this->$variable;
			}
		
		
		/** __set
		 *	
		 *	Sets a variable value.
		 *
		 */
			public function __set($variable, $value) {
				$this->$variable = $value;
			}
			
		/** __isset
		 *
		 *	Checks if a variable is set, returns true or false.
		 *
		 */
			public function __isset($variable) {
				if ( isset($this->$variable) && ! empty($this->$variable) ) {
					return true;
				}
				else {
					return false;	
				}
			}
						
				
	/* =============
		"API"
	============= */
	
		/**
		 *	META, HTML, OPTION
		 *	
		 *	Functions available:
		 *		get* 
		 *		set*
		 *		has*
		 */
				
			//	META
			
				public function getMeta($key) {				
					if ( isset($this->meta[$key]) )
						return $this->meta[$key];
				}
				public function setMeta( $name, $value ) {
					$this->meta[$name] = $value;
					return $this;
				}
				public function hasMeta($name) {				
					if ( isset($this->meta[$name]) && ! empty($this->meta[$name]) )
						return true;	
					else
						return false;
				}
		
						
			//	HTML
		
				public function getHtml($key) {
					if ( isset($this->html[$key]) )
						return $this->html[$key];
				}
				public function setHtml( $name, $value ) {
					$this->html[$name] = $value;
					return $this;
				}
				public function hasHtml($name) {				
					if ( isset($this->html[$name]) && ! empty($this->html[$name]) )
						return true;	
					else
						return false;
				}
			
			
			//	OPTION
			
				public function getOption($key) {
					if ( isset($this->options[$key]) )
						return $this->options[$key];
				}
				
				public function setOption( $name, $value ) {
					$this->options[$name] = $value;
					return $this;
				}
				
				public function hasOption($name) {				
					if ( isset($this->options[$name]) && ! empty($this->options[$name]) )
						return true;	
					else
						return false;
				}
		
		/**
		 *	DATA ARG, CSS CLASS
		 *	
		 *		get* 
		 *		add*
		 *		has*
		 */	
			
			//	DATA ARGS
	
				public final function getDataArg($key) {
					if ( isset($this->html['data_args'][$key]) )
						return $this->html['data_args'][$key];
				}
			
				public final function addDataArg( $name, $value = NULL ) {
					if ( is_array($name) ) {
						foreach($name as $k => $v) :
							$this->html['data_args'][$k] = $v;
						endforeach;
					}
					else {
						$this->html['data_args'][$name] = $value;
					}
					return $this;
				}
				
				public final function hasDataArg($name){
					if ( isset($this->html['data_args'][$name]) && ! empty($this->html['data_args'][$name]) )
						return true;	
					else
						return false;
				}
					
					// shorthand
					public final function addData($data, $value = NULL){
						$this->addDataArg($data, $value);				
					}
				
					// sometimes i forget...
					public final function setDataArg($name, $value = NULL){
						$this->addDataArg($name, $value);	
					}
				
			
			//	CSS CLASS
			
				public final function getCssClass($name) {
					if ( isset($this->html['css_class'][$name]) )
						return $this->html['css_class'][$name];
				}
			
				public final function addCssClass($name) {
					if ( is_array($name) ) {
						foreach($name as $c) :
							$this->html['css_class'][] = $c;
						endforeach;
					}
					else {
						$this->html['css_class'][] = $name;
					}
					return $this;
				}
				
				public final function hasCssClass($name){
					if ( isset($this->html['css_class'][$name]) && ! empty($this->html['css_class'][$name]) )
						return true;	
					else
						return false;
				}
			
		//	Capabilities
		
			public final function getCap($name) {
				if ( isset($this->caps[$name]) )
					return $this->caps[$name];
			}
		
			public final function setCap($name, $cap) {
				$this->caps[$name] = $cap;
				return $this;
			}
			
		
		
		/** is_single_value
		 *
		 *	Does this field only have 1 value? <- answers that question
		 *	
		 *	@deprecated
		 * 
		 *	GOING TO BE REMOVED SOON
		 */
		
			public function is_single_value() {
				
				if ( ! $this->getMeta('single') ) {
					$value = false;	
				}
				
				$value = apply_filters('xe/is_single_value', $value, $this->meta, $this->options);
				
				return (boolean) $value;	
												
			}
		
		/** set_input_type
		 *
		 *	Sets type of X-Editable input to use. Use as normal function or redefine.
		 * 
		 *	@param string input_type The type of X-Editable-compatible input to use.
		 * 	@link http://vitalets.github.io/x-editable/docs.html#inputs List of X-Editable inputs
		 * 
		 */
		 
			public function set_input_type( $input_type ) {
				
				$this->setOption('input_type', apply_filters('xe/input_type', 
					$input_type, 
					$this->meta, 
					$this->options
				) );
				
				return $this;
			}
	
	
	/* ================
		SHOW-ERS
	================ */
	
	
		/** showLabel
		 *
		 *	Shows a label.
		 * 
		 *	@param array attributes Attributes for label.
		 * 	@return string Echoes HTML output for label
		 *
		 */
			public final function showLabel(array $attributes = NULL) {
				
				$this->checkSetup();
								
				if ( ! $this->getCap('view') || current_user_can( $this->getCap('view') ) ) {
				
					do_action('xe/create_label', $this->meta, $attributes);	
				}
			}
		
		
		/** returnLabel
		 *
		 *	Returns a label.
		 * 
		 *	@param array attributes Attributes for label.
		 * 	@return string HTML output for label
		 *
		 */
			public final function returnLabel(array $attributes = NULL){
				
				$this->checkSetup();
								
				if ( ! $this->getCap('view') || current_user_can( $this->getCap('view') ) ) {
				
					do_action('xe/create_label', $this->meta, $attributes, true);
				}
			}
		
		
		/** showValues
		 *
		 *	Shows values externally.
		 * 
		 * 	@param boolean as_ul Whether to show values in an unordered list
		 * 	@return string HTML output for external values (e.g. a <ul>)
		 *
		 */ 
			public final function showValues( $as_ul = false ) {
				
				$this->addDataArg('external', true);
				
				$this->checkSetup();
				
				if ( ! $this->getCap('view') || current_user_can( $this->getCap('view') ) ) {
				
					do_action_ref_array('xe/create_external', array($this->id, $this->meta, $this->html, $as_ul));
				}
			}	
		
		
		/** returnValues
		 *
		 *	Returns values externally
		 * 
		 */
			public final function returnValues( $as_ul = false ) {
				
				$this->addDataArg('external', true);
				
				$this->checkSetup();
				
				if ( ! $this->getCap('view') || current_user_can( $this->getCap('view') ) ) {
				
					do_action_ref_array('xe/create_external', array($this->id, $this->meta, $this->html, $as_ul, true));
				}
			}
	
			
		/**	html
		 *
		 *	The element HTML output
		 *	
		 * 	@return string HTML output for element
		 * 
		 */
			
			public final function html() {
				
				$this->checkSetup();
				
				if ( ! $this->getCap('view') || current_user_can( $this->getCap('view') ) ) {
				
					do_action('xe/create_element', $this->meta, $this->id, $this->html, $this->options);
				}
			}
	
	
		/**	returnHtml
		 *
		 *	Returns element HTML output
		 *	
		 * 	@return string HTML output for element
		 * 
		 */
			
			public final function returnHtml() {
				
				$this->checkSetup();
				
				if ( ! $this->getCap('view') || current_user_can( $this->getCap('view') ) ) {
				
					do_action('xe/create_element', $this->meta, $this->id, $this->html, $this->options, true);
				}
			}
	

/* ---------------------------------
		PRIVATE & PROTECTED 
--------------------------------- */

	/**	set_value_and_output
	 *
	 *	Sets html['value'] and html['text'] vars.
	 *	Most fields should overwrite this function
	 */
		
		protected function set_value_and_text() {
			
			// Value
			
			$value = $this->getMeta('value');
			
			$this->setHtml('value', 
				apply_filters('xe/html/value', 
					$value, 
					$this->meta, 
					$this->options
				)
			);				
			
			
			/** ------------ Text ------------ */
			
			// empty
			// filter: xe/html/text/empty
			
			if ( empty($value) ) {
			
				$this->setHtml('text', 
					apply_filters('xe/html/text/empty', 
						'Empty', 
						$this->meta, 
						$this->options
					)
				);
			}
			
			// edit_link
			// filter: xe/html/text/edit_link
			
			elseif ( $this->getOption('edit_link') ) {
				
				$this->setHtml('text', 
					apply_filters('xe/html/text/edit_link', 
						'Edit', 
						$this->meta, 
						$this->options
					)
				);
			}
			
			// has value
			// filter: xe/html/text
			
			else {
			
				$this->setHtml('text', 
					apply_filters('xe/html/text', 
						$value, 
						$this->meta, 
						$this->options
					)
				);
			}
				
		}
	
	
	/**	setupSource
	 *
	 *	Sets html['source'] for fields with 'choices' (ACF only)
	 *	
	 *	Can be overwritten by fields
	 */
		protected function setupSource() {
			
			if ( isset($this->meta['choices']) ) {
				$this->setHtml('source', $this->to_json($this->meta['choices']));
			}					
		}
	
	
	/**	checkSetup
	 *
	 * 	Checks if field is setup and runs setup() if not.
	 */
		 	
		private function checkSetup(){		
			if ( ! $this->_is_setup ) {
				$this->setup();
			}
		}
		
	
	/** setup
	 * 
	 *	 Performs field setup
	 */
		private function setup() {
			
			$this->set_value_and_text();
			$this->setupCssClass();
			$this->setupSource();
			
			$this->_is_setup = true;	
		
			do_action('xe/field_setup', $this);
			
		}
	
	
	/** setupCssClass
	 *
	 * 	Adds CSS classes to the element
	 */
		private function setupCssClass() {
			
			$this->addCssClass('x-editable-element');
		
			if ( $this->is_single_value() )
				$this->addCssClass('single-value');
			
			if ( $this->getOption('show_external') || $this->getDataArg('external') ) 
				$this->addCssClass('values-external');
			
			if ( $this->getDataArg('edit_link') 
			|| $this->getOption('edit_link') 
			|| 'edit' === strtolower($this->getHtml('text')) )		
				$this->addCssClass('edit-link');

			$this->html['css_class'] = apply_filters('xe/html/css_class', 
				$this->html['css_class'], 
				$this->meta, 
				$this->options
			);
		}
	
		
	/** to_json
	 *	
	 *	Helper function to json_encode a variable as array string or objects (default)
	 * 
	 * 	@param mixed data The data to jsonify and return
	 * 	@param boolean as_string Whether to return as JSON string (default:false). If false, uses JSON_FORCE_OBJECT
	 *	@return string JSON-encoded string
	 */
		public function to_json( $data, $as_string = false ) {
			
			// Don't restrict user input to existing choices
			if ( $as_string || ! $this->getOption('restrict_choices') ) {
				$json = json_encode($data);
			}
			// Restrict user input to existing choices
			else {
				$json = json_encode($data, JSON_FORCE_OBJECT);	
			}
			
			return $json;
		}

}

?>