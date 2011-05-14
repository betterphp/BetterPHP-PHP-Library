<?php

/*
 *
 *		This file provides functions that php should have but doesn't.
 *
 */

// checks to see if $haystack starts with $needle
function str_starts_with($needle, $haystack){
	$needle_len = strlen($needle);
	
	return ($needle_len <= strlen($haystack) && substr($haystack, 0, $needle_len) === $needle);
}

// checks to see if $haystack ends with $needle
function str_ends_with($needle, $haystack){
	$needle_len = strlen($needle);
	
	return ($needle_len <= strlen($haystack) && substr($haystack, -$needle_len) === $needle);
}

// checks to see if $haystack contains $needle
function str_contains($needle, $haystack){
	return (strpos($haystack, $needle) !== false);
}

// checks to see if strlen($subject) is between $x and $y ($x should be lower)
function strlen_between($subject, $x, $y){
	$len = strlen($subject);
	
	return (($len > $x) && ($len < $y));
}

?>
