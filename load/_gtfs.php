<?php

	/*
		
	*/


	// check includes
	if(!function_exists("sql")) {
		die(" ! Missing SQL class.");
	}

	// Convert CSV file in MySQL table and data
	function insert_gtfs($file) {
		
		// folder name = zip name
		$network = str_replace(".zip", "", $file);
			
		// work path (full or relative)
		$path = "./".$network."/";
		
		// what we need
		// https://developers.google.com/transit/gtfs/reference
		$expected_files = array();
		$expected_field = array();
		
		$expected_field['default']						= "VARCHAR(127) DEFAULT NULL";
		
		// stops
		$expected_files[] = "stops";
		$expected_field['stops']['stop_id']				= "VARCHAR(127) DEFAULT NULL";
		$expected_field['stops']['stop_lat']			= "FLOAT DEFAULT NULL";
		$expected_field['stops']['stop_lon']			= "FLOAT DEFAULT NULL";
		$expected_field['stops']['stop_name']			= "VARCHAR(127) DEFAULT NULL";
		
		// routes
		$expected_files[] = "routes";
		$expected_field['routes']['route_id']			= "VARCHAR(127) DEFAULT NULL";
		$expected_field['routes']['route_short_name']	= "VARCHAR(127) DEFAULT NULL";
		$expected_field['routes']['route_long_name']	= "VARCHAR(127) DEFAULT NULL";
		$expected_field['routes']['route_type']			= "INT(1) DEFAULT NULL";    
		
		// trips
		$expected_files[] = "trips";
		$expected_field['trips']['route_id']			= "VARCHAR(127) DEFAULT NULL";
		$expected_field['trips']['service_id']			= "VARCHAR(127) DEFAULT NULL";
		$expected_field['trips']['trip_id']				= "VARCHAR(127) DEFAULT NULL";
		$expected_field['trips']['trip_headsign']		= "VARCHAR(127) DEFAULT NULL";    // -- Need for my project
		
		// stop_times
		$expected_files[] = "stop_times";
		$expected_field['stop_times']['trip_id']		= "VARCHAR(127) DEFAULT NULL";
		$expected_field['stop_times']['arrival_time']	= "TIME DEFAULT NULL";	// hh:mm:ss format
		$expected_field['stop_times']['departure_time']	= "TIME DEFAULT NULL";	// hh:mm:ss format
		$expected_field['stop_times']['stop_id']		= "VARCHAR(127) DEFAULT NULL";
		$expected_field['stop_times']['stop_sequence']	= "INT(3) DEFAULT NULL";
		
		// calendar
		$expected_files[] = "calendar";
		$expected_field['calendar']['service_id']		= "VARCHAR(127) DEFAULT NULL";
		$expected_field['calendar']['monday']			= "INT(1) DEFAULT NULL";
		$expected_field['calendar']['tuesday']			= "INT(1) DEFAULT NULL";
		$expected_field['calendar']['wednesday']		= "INT(1) DEFAULT NULL";
		$expected_field['calendar']['thursday']			= "INT(1) DEFAULT NULL";
		$expected_field['calendar']['friday']			= "INT(1) DEFAULT NULL";
		$expected_field['calendar']['saturday']			= "INT(1) DEFAULT NULL";
		$expected_field['calendar']['sunday']			= "INT(1) DEFAULT NULL";
		$expected_field['calendar']['start_date']		= "INT(8) DEFAULT NULL";	// YYYYMMDD format
		$expected_field['calendar']['end_date']			= "INT(8) DEFAULT NULL";	// YYYYMMDD format
		
		// calendar_dates -- Need for my project
		$expected_files[] = "calendar_dates";
		$expected_field['calendar_dates']['service_id']		= "VARCHAR(127) DEFAULT NULL";
		$expected_field['calendar_dates']['date']			= "INT(8) DEFAULT NULL";	// YYYYMMDD format
		$expected_field['calendar_dates']['exception_type']	= "INT(1) DEFAULT NULL";
	
		//----------------------------------------------//
				
		// Check if all required fields are here
		foreach($expected_files as $file) {

			// We add a blank line for log.
			echo("".PHP_EOL);
						
			echo("Working with ".$file.".txt".PHP_EOL);
			
			// check if the file exist
			if(!file_exists($path.$file.".txt")) {
				die(" -> ERROR : The file was not found in '".$network."'!");
			}
			
			// try to open the file
			$handle = @fopen($path.$file.".txt", "r");
			
			// if we have an error
			if (!$handle) {
				die(" -> ERROR : The file can't be openned !");			
			}
			
			// get the first line
			$buffer = fgets($handle, 4096);
			
			// remove the UTF-8 BOM
			$buffer = str_replace("\xef\xbb\xbf", '', $buffer);
			
			// remove end line
			$buffer = trim($buffer);
			
			// explode the line to get the columns
			$fields = explode(",", $buffer);
	
			// check if we find all required fields
			$required = array_keys($expected_field[$file]);
			foreach($required as $field) {
				if(!in_array($field, $fields)) {
					die(" -> ERROR : The field '".$field."' is missing !");
				}
			}
			
			echo(" -> CHECK : OK".PHP_EOL);
						
			// we build the table
			$columns = array();
			foreach($fields as $field) {
				// if the field is a required (and sized) field, else we use default field.
				if(array_key_exists($field, $expected_field[$file])) {
					$columns[] = $field." ".$expected_field[$file][$field];
				} else {
					$columns[] = $field." ".$expected_field['default'];			
				}
			}
			
			$table = $network."_".$file;
			
			$sql_create  = "DROP TABLE IF EXISTS ".$table.";";
			$sql_create .= "CREATE TABLE ".$table."(";
			$sql_create .= implode(",", $columns);
			$sql_create .= "\n);";
			
			// Execute
			sql($sql_create);			
			
			$sql_load  = " LOAD DATA LOCAL INFILE '".realpath($path.$file.".txt")."'";
			$sql_load .= " INTO TABLE ".$table;
			$sql_load .= " FIELDS TERMINATED BY ','";
			$sql_load .= " OPTIONALLY ENCLOSED BY '". '"'."'";
			$sql_load .= " LINES TERMINATED BY '\n'";
			$sql_load .= " IGNORE 1 LINES;";
			
			// Execute
			sql($sql_load);
			
			echo(" -> INSERT : DONE".PHP_EOL);
	
		}	
	}
	