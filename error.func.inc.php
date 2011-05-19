<?php

/*
 *
 *		Effectivly the same as trigger_error, but shows the right file
 *		and line numbers when used within a function.
 *
 */

function custom_error($msg){
	if (ini_get('display_errors') && error_reporting() > 0){
		$info		= next(debug_backtrace());
		
		$prepend	= ini_get('error_prepend_string');
		$append		= ini_get('error_append_string');
		
		echo "{$prepend}\nWarning: {$msg} in {$info['file']} on line {$info['line']}\n{$append}\n\n";
	}
}

?>
