<pre>
<?php

	/*
		Main import		
	*/	

	// reporting all errors to see what we do...
	error_reporting(E_ALL);

	// loading some files
	include_once('./../load/_sql.php');
	include_once('./../load/_zip.php');
	include_once('./../load/_gtfs.php');
	
	// list all zip file in the folder 
	$files = glob("*.zip");
	
	// working on this files
	foreach($files as $file) {
		extract_gtfs($file);
		insert_gtfs($file);
	}

	// We add a blank line for log.
	echo("".PHP_EOL);
	
	// Now we build a big table with the position of each stops (with id, lat, lon, name and network name).
	$item['stop_id']		= "VARCHAR(127) DEFAULT NULL";
	$item['stop_lat']		= "FLOAT DEFAULT NULL";
	$item['stop_lon']		= "FLOAT DEFAULT NULL";
	$item['stop_name']		= "VARCHAR(127) DEFAULT NULL";
	$item['stop_service']	= "VARCHAR(127) DEFAULT NULL";
	
	$keys = array_keys($item);
	$columns = array();
	foreach($keys as $key) {
		$columns[] = $key." ".$item[$key];			
	}
				
	// drop and create			
	$sql_create  = "DROP TABLE IF EXISTS big_table;";			
	$sql_create .= "CREATE TABLE big_table (";
	$sql_create .= implode(",", $columns);
	$sql_create .= "\n);";
	
	sql($sql_create);

	// search all tables we need	
	$tables = sql("SELECT table_name FROM information_schema.tables WHERE table_name LIKE '%_stops';");
	
	foreach($tables as $table) {
		$name = str_replace("_stops", "", $table['table_name']);
		$sql  = "INSERT INTO big_table (stop_id, stop_lat, stop_lon, stop_name, stop_service) ";
		$sql .= "SELECT stop_id, stop_lat, stop_lon, stop_name, '".$name."' FROM ".$table['table_name']."";
		sql($sql);		
		echo("Insert ".$name." into the big table : DONE".PHP_EOL);		
	}

	// It's done.
