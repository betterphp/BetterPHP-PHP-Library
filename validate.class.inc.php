<?php

/*
 *
 *		This class a generic way to interact with the validate information.
 * 
 *		Requirements	- mysql.class.inc.php
 *						- array.func.inc.php
 *
 */

class validate {
	
	// checks to see if the given combinations of columns and values is in the given table.
	public static function mysql_unique($table_name, $field_name, $field_value){
		global $mysql;
		
		$field_value	= trim($field_value);
		$field_name		= trim($field_name);
		$table_name		= trim($table_name);
		
		if (empty($field_value) || empty($field_name) || empty($table_name)){
			return false;
		}
		
		if (is_array($field_value)){
			foreach ($field_value as $k => $value){
				$mysql->escape($value);
				$mysql->escape($field_name[$k]);
				
				$conditions[] = "`{$field_name[$k]}` = '{$value}'";
			}
			
			$mysql->escape($table_name);
			
			$query = $mysql->query("SELECT COUNT(1) FROM `{$table_name}` WHERE " . implode(' AND ', $conditions));
		}else{
			$mysql->escape($table_name);
			$mysql->escape($field_name);
			$mysql->escape($string);
			
			$mysql->query("SELECT COUNT(1) FROM `{$table_name}` WHERE `{$field_name}` = '{$field_value}'");
		}
		
		return ($mysql->fetch_cell($query) != 0);
	}
	
		// validates an email address
	public static function email($email){
		if (strlen($email) > 320){
			return false;
		}
		
		// if we can use filter_var() do so, if not use the regex from the php source.
		// http://svn.php.net/viewvc/php/php-src/trunk/ext/filter/logical_filters.c?view=markup line 525
		if (is_callable('filter_var') && filter_var($email, FILTER_VALIDATE_EMAIL) === false){
			return false;
		}else if (preg_match('/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD', $email) === 0){
			return false;
		}
		
		$bits = explode('@', $email);
		
		if (strlen($bits[0]) > 64 || strlen($bits[1]) > 255){
			return false;
		}
		
		return true;			
	}
	
	// validates a url.
	public static function url($url){
		// use filter_Var() if we can, if not a regex method, taken from
		// http://flanders.co.nz/2009/11/08/a-good-url-regular-expression-repost/
		if (is_callable('filter_var') && filter_var($url, FILTER_VALIDATE_URL) === false){
			return false;
		}else if (preg_match('/^(?#Protocol)(?:(?:ht|f)tp(?:s?)\:\/\/|~\/|\/)?(?#Username:Password)(?:\w+:\w+@)?(?#Subdomains)(?:(?:[-\w]+\.)+(?#TopLevel Domains)(?:com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum|travel|[a-z]{2}))(?#Port)(?::[\d]{1,5})?(?#Directories)(?:(?:(?:\/(?:[-\w~!$+|.,=]|%[a-f\d]{2})+)+|\/)+|\?|#)?(?#Query)(?:(?:\?(?:[-\w~!$+|.,*:]|%[a-f\d{2}])+=?(?:[-\w~!$+|.,*:=]|%[a-f\d]{2})*)(?:&(?:[-\w~!$+|.,*:]|%[a-f\d{2}])+=?(?:[-\w~!$+|.,*:=]|%[a-f\d]{2})*)*)*(?#Anchor)(?:#(?:[-\w~!$+|.,*:=]|%[a-f\d]{2})*)?$/i', $url) === 0){
			return false;
		}
		
		if (($headers = get_headers($url)) === false){
			return false;
		}
		
		if (str_contains('200', $headers[0]) === false){
			return false;
		}
		
		return true;
	}
	
}

?>
