<?php

	/*
		Function to extract a GTFS zip file
		$file = gtfs-file.zip
		
		trigger_error is to log the process
	*/

	
	function extract_gtfs($file) {
		
		// We add a blank line for log.
		echo("".PHP_EOL);
			
		echo("Working on a new GTFS zip : ".$file.PHP_EOL);
	
		// folder name = zip name
		$network = str_replace(".zip", "", $file);
		
		// secure the shell command
		$shell = escapeshellcmd("unzip ".$file." -d ".$network);
		
		// exec the unzip command
		shell_exec($shell);
		
		echo(" -> Extract ".$network." done.".PHP_EOL);
		
		// check if the unzip was really done (search stops.txt)
		$file = realpath($network)."/stops.txt";
		
		if(file_exists($file)) {
			echo(" -> CHECK : OK".PHP_EOL);
		} else {
			die(" -> ERROR : The file 'stops.txt' was not found in ".$network." during the check.");
		}
	}