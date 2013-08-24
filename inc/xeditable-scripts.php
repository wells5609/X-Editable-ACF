<?php header('Content-type: application/javascript');


function print_scripts(){

	$js = $GLOBALS['xe-scripts'];
	$paths = array();
	
	foreach($js as $script => $path):	
		include $path;
	endforeach;	

}

print_scripts();

?>