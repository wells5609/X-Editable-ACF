<?php
//require_once realpath('../../../../wp-load.php');

class XE_Scripts {

	public static $expires_offset = 7776000; // 3 months
	public static $required_files = array(
		'bs' => 'bootstrap-editable.min.js',
		'xe' => 'x-editable-acf.js',
	);
	public static $input_files = array(
		'text' => 'js/text.js',
		'textarea' => 'js/textarea.js',
		'select' => 'js/select.js',
		'date' => 'js/date.js',
		'checklist' => 'js/checklist.js',
		'html5' => 'js/html5.js',
		'typeahead' => 'js/typeahead.js',
	);
	public static $user_files = array();
	
	public static function init(){
		//self::$user_files = apply_filters('xe/user_scripts', self::$user_files);
	}
	
	public static function add($input, $filepath){
		self::$user_files[$input] = $filepath;	
	}
	
	public static function getScript($name){
		$str = '';
		
		if ( in_array($name, array_keys(self::$required_files)) ){
			$js = self::$required_files[$name];
		}
		else if ( in_array($name, array_keys(self::$input_files)) ){
			$js = self::$input_files[$name];	
		}
		if ( $js )
			return get_file($js);
		
		if ( in_array($name, array_keys(self::$user_files)) ){
			$js = self::$user_files[$name];
			if ( is_array($js) ) {
				foreach($js as $script):
					$str .= get_file($script);
				endforeach;	
			}
			else
				$str .= get_file($js);
		}
		if ( $str )
			return $str;
	}
	
	public static function getRequiredScripts(){
		$str = '';
		foreach(self::$required_files as $key => $path):
			$str .= get_file($path);
		endforeach;
		return $str;
	}
	
	
	public static function getScriptNames($type = false){
		if ( ! $type )
			$scripts = array_merge(
				self::$required_files,
				self::$input_files,
				self::$user_files
			);
		elseif ( 'user' === $type )
			$scripts = self::$user_files;	
		elseif ('core' === $type)
			$scripts = self::$input_files;
		elseif ('required' === $type)
			$scripts = self::$required_files;
		
		return $scripts;	
	}

}

// helpers
function get_file($path){
	
		if ( function_exists('realpath') )
			$path = realpath($path);
	
		if ( ! $path || ! @is_file($path) )
			return '';
	
		return @file_get_contents($path);
	}