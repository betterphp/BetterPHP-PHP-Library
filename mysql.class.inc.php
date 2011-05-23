<?php

/*
 *
 *		Handles mysql connections and queries.
 *
 */

class mysql {
	
	private $link		= null;
	private $result		= null;
	
	// connects to the database.
	public function __construct($server, $user, $pass, $db){
		$this->link = mysql_pconnect($server, $user, $pass);
		mysql_select_db($db, $this->link);
		
		if (is_callable('get_magic_quotes_gpc') && get_magic_quotes_gpc() === 1){
			foreach ($_GET as &$value) $value = stripslashes($value);
			foreach ($_POST as &$value) $value = stripslashes($value);
			foreach ($_COOKIE as &$value) $value = stripslashes($value);
		}
	}
	
	// escapes any control character in the input.
	public function escape(&$var, $return = false){
		$var = mysql_real_escape_string($var, $this->link);
		
		if ($return) return $var;
	}
	
	// performs the given SQL query.
	public function query($sql){
		$this->result = mysql_query($sql, $this->link);
	}
	
	// same as above but unbuffered.
	public function ub_query($sql){
		$this->result = mysql_unbuffered_query($sql, $this->link);
	}
	
	// fetches a row following a query.
	public function fetch(&$row){
		$row = mysql_fetch_assoc($this->result);
		
		return ($row !== false);
	}
	
	// same as above but returns an array of rows.
	public function fetch_array(){
		$results = array();
		
		while ($this->fetch($row)){
			$results[] = $row;
		}
		
		return $results;
	}
	
	// fetches a single cell.
	public function fetch_cell(&$result){
		$result = mysql_result($this->result, 0);
		
		return ($result !== false);
	}
	
	// fetches the last auto_increment number.
	public function fetch_last_id(&$id){
		$id = mysql_insert_id($this->link);
	}
	
}

?>
