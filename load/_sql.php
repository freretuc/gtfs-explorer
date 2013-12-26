<?php

	/*
		MySQL singelton class
	*/
	
	// must define some elements from MySQL
	define('DB_HOST', '****');
	define('DB_USER', '****');
	define('DB_PASS', '****');
	define('DB_BASE', '****');

	define('DB_CONN', 'mysql:host='.DB_HOST.';dbname='.DB_BASE);

	// The singelton class.
	class mSQL extends PDO {
	    private static $_instance;
	    public function __construct () {}
	    private function __clone () {}
	    public static function getInstance () {
	        if(!isset(self::$_instance)) 
	            self::$_instance = new PDO(DB_CONN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	        return self::$_instance;
	    }
	}

	// main function to dial with the MySQL server
	function sql($sql) {
		if(!$rs = mSQL::getInstance()->query($sql)) {
			$msg = mSQL::getInstance()->errorInfo();
				if(true) {
					die("ERROR : ".$msg[2]."\n\n".$sql);
				}
		}
		return $rs->fetchAll(PDO::FETCH_ASSOC);
	}
	
	// function to update some data in the table
	function update($table, $key, $data) {
		$id = array_keys($key);
		$id = $id[0];
		$cle = $key[$id];
		$update = '';
		foreach($data as $field => $value) { $update .= " ".$field." = '".addslashes($value)."',"; }
		$update = substr($update, 0, -1);
		sql("UPDATE ".$table." SET ".$update." WHERE ". $id ." = '".$cle."';");
	}
	
	// function to insert data in the base
	function insert($table, $data) {
		$f = array();
		$v = array();
		foreach($data as $field => $value) {
			$f[] = $field;
			$v[] = "'" . addslashes($value) . "'";
		}
		sql("INSERT INTO ".$table."(".implode(',', $f).") VALUES (".implode(',', $v).");");
	}