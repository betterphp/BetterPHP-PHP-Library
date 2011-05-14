<?php

/*
 *
 *		This file loads all file in the same folder as it.
 *
 */

foreach (glob(dirname(__FILE__) . DIRECTORY_SEPARATOR . '*.inc.php') as $file){
	include($file);
}

?>
