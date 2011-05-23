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
	
}

?>
