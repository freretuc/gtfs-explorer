<?php
	/*
		Sending time for the stop	
	*/
	
	// we send json files...
	header('Content-type: text/json; charset=utf-8');
	header('Content-type: application/json; charset=utf-8');
	
	// we load the SQL functions
	include_once("./load/_sql.php");

	// assigning some values
	$weekday = strtolower(strftime('%A'));	
	$date = date("Ymd");
	$time = date("H:i:00");
	$network = $_GET['network'];
	$stopid = $_GET['stop_id'];

	// We search the stop from big_table in the local_table
	$arret = sql("SELECT * FROM ".$network."_stops WHERE stop_id = '".$stopid."'");
	$arret = $arret[0];

	// if the stop_id are different, we have a problem...
	if($arret['stop_id'] != $stopid) {
		exit();
	}

	$result = array();
	
	// searching service_id in exceptional days and normal day
	$sql  = "SELECT service_id FROM ".$network."_calendar_dates WHERE date = '".$date."' AND exception_type = 1 ";
	$sql .= "UNION ";
	$sql .= "SELECT service_id FROM ".$network."_calendar WHERE ".$weekday." = 1 AND start_date < '".$date."' AND end_date > '".$date."'";
	$service_id = sql($sql);
	
	// extract the data
	$in_service_id = array();
	foreach($service_id as $item) {
		$in_service_id[] = "'".$item['service_id']."'";
	}
	
	// grab trip_id and trip_headsign (for display) in the trips where the service_id is available now
	$sql = "SELECT trip_id, trip_headsign FROM ".$network."_trips WHERE service_id IN (".implode(",", $in_service_id).")";
	$trip_id = sql($sql);
	
	// extract the data
	$trip_headsign = array();
	$in_trip_id = array();
	foreach($trip_id as $item) {
		$in_trip_id[] = "'".$item['trip_id']."'";
		$trip_headsign[$item['trip_id']] = $item['trip_headsign'];
	}
	
	// serach next stop times
	$sql = "SELECT departure_time, trip_id FROM ".$network."_stop_times WHERE departure_time > '".$time."' AND stop_id = '".$stopid."' AND trip_id IN (".implode(",", $in_trip_id).") ORDER BY departure_time LIMIT 5";
	$horaires = sql($sql);
	
	// extract the data
	// we clean the data : removing seconds in departure_time, and uppercase to headsign
	$items = array();
	foreach($horaires as $item) {
		$temp_array = array();
		$temp_array['time'] = substr($item['departure_time'],0,-3);
		$temp_array['headsign'] = strtoupper($trip_headsign[$item['trip_id']]);
		$items[] = $temp_array;
	}
	
	$result['size'] = count($items);
	$result['items'] = $items;
	$result['stop'] = $arret;
	
	// sending
	echo json_encode($result);