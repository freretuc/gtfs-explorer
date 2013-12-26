<!doctype html>
<html lang="fr">
<head>
    <style>
		* {
			margin: 0;
			padding: 0;
		}
		html, body, #map-canvas {
			height: 100%;
			margin: 0px;
			padding: 0px;
			font-family: "Trebuchet MS";
			font-size: 12px;
		}
		#display-data {
			background: none repeat scroll 0 0 rgba(255, 255, 255, 0.8);
			padding: 4px;
			position: fixed;
			left: 0;
			top: 0;	
			width: 300px;	
		}		
		p {
		margin: 0;
		}
	</style>
	<title>BigMap</title>
	<meta charset="utf-8">
	<script src="jq-min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
    <script>
    
    var map;
	var markersArray = [];
	var dest;
	var time;
			
	// Get current position
	function getPosition(position) {
		var newPosition = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
		map.setCenter(newPosition);
	}
	
	// Initialize map
	function initialize() {
		var mapOptions = {
			zoom: 13,
			disableDefaultUI: true,
			center: new google.maps.LatLng(-34.397, 150.644),
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
		google.maps.event.addListener(map, 'idle', mapIdle);
	}

	// Clean markers on the map
	function cleanMap() {
		for (var i=0; i<markersArray.length; i++) {
			markersArray[i].setMap(null);
		}
	}

	// Add a marker on the map
	function addMarker(location, title, code) {
		marker = new google.maps.Marker({
			position: location,
			title: title,
			map: map,
			icon: 'http://labs.google.com/ridefinder/images/mm_20_orange.png'
		});
		google.maps.event.addListener(marker, 'click', function() {
			$.getJSON("get-time?stop_id=" + code, returnTime );
		});
		markersArray.push(marker);		
	}

	// Idle function, get the stops in this bounds
	function mapIdle() {
		var nelat = map.getBounds().getNorthEast().lat();
		var nelng = map.getBounds().getNorthEast().lng();
		var swlat = map.getBounds().getSouthWest().lat();
		var swlng = map.getBounds().getSouthWest().lng();
		var zoom = map.getZoom();
		$.getJSON("get-stops?nelat=" + nelat + "&nelng=" + nelng + "&swlat=" + swlat + "&swlng=" + swlng + "&zoom=" + zoom, returnStops);
	}

	// Display stops on the map
	function returnStops(data) {
		cleanMap();
		if(data.size > 0) {
			$.each(data.items, function(i, item) {
				var LatLng = new google.maps.LatLng(item.lat, item.lng);
				addMarker(LatLng, item.name, item.code);
			});
		}
	}

	// Display next time for the stop
	function returnTime(data) {
		 dest = [];
		 time = [];
		var html = "<p><b>" + data.stop.stop_name + "</b></p>";
		if(data.size > 0) {
			$.each(data.items, function(i, item) {
				if(dest.indexOf(item.headsign) == -1) {
					dest.push(item.headsign);
					time.push([]);
					time[dest.indexOf(item.headsign)].push(item.time);
				} else {
					time[dest.indexOf(item.headsign)].push(item.time);
				}
			});		
			for(i=0; i<dest.length; i++) {
				html+="<p>"+dest[i]+"</p><ul>";
				html+="<li>"+time[i].join(" - ")+"</li>";
				html += "</ul>";
			}
		} else {
			html += "<p>Nothing here...</p>";
		}		
		$('#time').html(html);
	}

	// On load
	$(document).ready(function() {
		initialize();
		navigator.geolocation.getCurrentPosition(getPosition);
	});

    </script>
  </head>
  <body>
    <div id="map-canvas"></div>
    <div id="display-data">
    	<div id="time"></div>
    </div>
    <div id="cities">
</body>
</html>
