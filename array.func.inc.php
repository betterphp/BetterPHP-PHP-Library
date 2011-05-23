<?php

/*
 *
 *		This file provides some array functions that php should
 *		have, but for some reason, does not.
 *
 */

// returns true if the given array is multidimensional, false if not.
function array_is_multi($array){
	return (count($array) === count($array, COUNT_RECURSIVE));
}

?>
