<?php
require_once 'xe-scripts.php';
$out = '';

// Input JS files
if( $_GET['load'] ){
	
	// Required JS files
	$out .= XE_Scripts::getRequiredScripts();

	$load = explode(',', $_GET['load']);
	
	foreach( $load as $file ) {
		
		$out .= XE_Scripts::getScript( $file ) . "\n";
	}
}

$expires_offset = XE_Scripts::$expires_offset;

header('Content-Type: application/x-javascript; charset=UTF-8');
header('Expires: ' . gmdate( "D, d M Y H:i:s", time() + $expires_offset ) . ' GMT');
header("Cache-Control: public, max-age=$expires_offset");

// Compress (maybe)
if ( 1 == $_GET['c'] || 'gzip' == $_GET['c'] ) {
	
	if ( ! ob_start("ob_gzhandler") ) ob_start();
}
else ob_start();

echo $out;

ob_flush();
?>