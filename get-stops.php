<?php
	/*
		Sending stops in the area	
	*/
	
	// we send json files...
	header('Content-type: text/json; charset=utf-8');
	header('Content-type: application/json; charset=utf-8');
	
	// we load the SQL functions
	include_once("./load/_sql.php");
	
	// cleaning bounds and zoom
	$nelat = floatval($_GET['nelat']);
	$nelng = floatval($_GET['nelng']);
	$swlat = floatval($_GET['swlat']);
	$swlng = floatval($_GET['swlng']);
	$zoom = intval($_GET['zoom']);
	
	// clean the response array
	$result = array();
	
	if($zoom < 13) {
		$result['msg'] = "wrong zoom";
	} else {
		$sql = "SELECT * FROM big_table WHERE stop_lat > ".$swlat." AND stop_lat < ".$nelat." AND stop_lon > ".$swlng." AND stop_lon < ".$nelng.";";
		$stops = sql($sql);
		$values = array();
		// parse the results
		foreach($stops as $s) {
			$val = array();
			$val['lat'] = $s['stop_lat'];
			$val['lng'] = $s['stop_lon'];
			$val['name'] = $s['stop_name'];
			$val['code'] = $s['stop_id'].'&network='.$s['stop_service'];
			$values[] = $val;
		}
		$result['size'] = count($stops);				
		$result['items'] = $values;
	}
	
	// sending
	echo json_encode($result);
