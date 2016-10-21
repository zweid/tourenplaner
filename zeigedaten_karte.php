<?php

include("../../GeneratedItems/config.php");
$checkrecht="touren";
include("../rechtecheck.php");
include("../../GeneratedItems/debug.php");
include("../standard/funktionen.tri_private.php");
include("../allgfunktionen.php");
include("../kunden/funktionen.php");
include("funktionen.php");

//pre($_REQUEST,4);



$sql_tourenanzeige  = "SELECT * FROM touren_kunden_zuordnung WHERE tour=$ID AND tourplanen=1 ORDER BY prio ASC ";
$res_tourenanzeige  = tri_db_query($GLOBALS['datenbanknamecms'], $sql_tourenanzeige);
while($row_tourenanzeige = mysql_fetch_assoc($res_tourenanzeige))
{
	$strasse	    = kunden_datenfelder($row_tourenanzeige['kundennummer'],'strassenfeld');
	$plz		    = kunden_datenfelder($row_tourenanzeige['kundennummer'],'plzfeld');
	$ort		    = kunden_datenfelder($row_tourenanzeige['kundennummer'],'ortsfeld');
	$koordinaten    = touren_geo_google_koordinaten($strasse,$plz,$ort);
	$auswahl[]      = array('kundennummer'  => $row_tourenanzeige['kundennummer'],
							'tour'          => $touranzeige,
							'koordinaten'   => $koordinaten,
							'prio'          => $row_tourenanzeige['prio']);
}
$google_api = trieinstellungauslesen('administration','system','google_api' );
if(trim($google_api)=='')
{
	$google_api		= 'AIzaSyBmsl7HIjIoZD-lM6gcH6YN-Hpcrlff5DA';
}
$google_maps    = "<script type=\"text/javascript\"
				      src=\"http://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true\">
				    </script>";
if($auswahl!='')
{
	$count  = count($auswahl)-1;
	$numkey = 1;
	foreach($auswahl as $kundenkey => $kundenrow)
	{
		$js_start           = '"'.$auswahl[0]['koordinaten'].'"';
		$js_end             = '"'.$auswahl[$count]['koordinaten'].'"';
		$jsarray_string     .= ($count>$numkey) ?    '{location:"'.$auswahl[$numkey]['koordinaten'].'"}, '  : ']';
		$numkey++;
	}
	if($count==1)
	{
		$jsarray    = '';
	}
	else {
		$jsarray = '[' . $jsarray_string;
		$jsarray = str_ireplace('}, ]]', '}]', $jsarray);
		$jsarray = 'waypoints: '.$jsarray.',';
	}
}
else
{
	$js_start           = '"52.5163890,13.3808330"';
	$js_end             = '"52.5163890,13.3808330"';
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
	<title>Tourenplaner</title>
	<?php
		echo tri_header_includes();
		echo $google_maps;
	?>
	<link rel="stylesheet" type="text/css" href="style.google.maps.css">
	<link rel="stylesheet" type="text/css" href="css/box_slideout.css">
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="//esri.github.io/bootstrap-map-js/src/css/bootstrapmap.css">
	<script type="text/javascript" src="js/mobilise.js"></script>
</head>
<body class="frame_bg_data" onload="calcRoute()">
<div id="content">
	<header id="site-header">
		<div id="header-content" class="clearfix"><div>
	</header>

	<div class="panel panel-primary panel-fixed-top">
		<div class="panel-heading">
			<h3 class="panel-title">Details zur gew&auml;hlen Route</h3>
		</div>
		<div id="total"></div>
		<div class="panel-body"></div>
	</div>


	<div id="grid_left">

	</div>
	<div class="grid_middle" >
		<div id="map_canvas"></div>
	</div>
	<div class="grid_right">
		<div id="directions_Panel"></div>
	</div>
</div>

<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
	_uacct = "UA-162157-1";
	urchinTracker();
</script>
<script type="text/javascript">


	var styler = [
		{
			stylers: [
				{ saturation: -99 },
				{ visibility: "simplified" },
				{ lightness: 30 }
			]
		},{
			featureType: "water",
			stylers: [
				{ saturation: 10 },
				{ hue: "#0091ff" },
				{ lightness: -19 },
				{ visibility: "simplified" }
			]
		},{
			featureType: "road",
			elementType: "geometry",
			stylers: [
				{ visibility: "simplified" },
				{ lightness: 100 }
			]
		},{
			featureType: "road",
			elementType: "labels",
			stylers: [
				{ visibility: "on" },
				{ saturation: -98 },
				{ lightness: 30 }
			]
		},{
			featureType: "poi.park",
			elementType: "geometry",
			stylers: [
				{ visibility: "on" },
				{ hue: "#00ff19" },
				{ saturation: 10 }
			]
		},{
			featureType: "administrative.land_parcel",
			stylers: [
				{ hue: "#ff001a" },
				{ saturation: 95 },
				{ visibility: "off" }
			]
		}
	];


	var request;
	request = {
		origin: <?php echo $js_start; ?>,
		destination: <?php echo $js_end; ?>,
		<?php echo $jsarray; ?>
		optimizeWaypoints: false,
		travelMode: google.maps.TravelMode.DRIVING
	};

	var directionsDisplay = new google.maps.DirectionsRenderer();
	var directionsService = new google.maps.DirectionsService();
	var map;

	function initialize() {

		drawMap();
		calcRoute();
	}

	function drawMap() {

		var start = new google.maps.LatLng(52.5163890, 13.3808330);
		var styledMap = new google.maps.StyledMapType(styler);
		var myOptions = {
			zoom:4,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			center: start,
			streetViewControl: false,
			mapTypeControlOptions: {
				mapTypeIds: [google.maps.MapTypeId.SATELLITE, 'map_style']
			}
		}
		map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		map.mapTypes.set('map_style', styledMap);
		map.setMapTypeId('map_style');
		directionsDisplay.setMap(map);
		directionsDisplay.setPanel(document.getElementById('directions_Panel'));
	}

	function getRendererOptions(main_route)
	{
		if(main_route)
		{
			var _colour = '#00458E';
			var _strokeWeight = 4;
			var _strokeOpacity = 1.0;
			var _suppressMarkers = false;
		}
		else
		{
			var _colour = '#ED1C24';
			var _strokeWeight = 2;
			var _strokeOpacity = 0.7;
			var _suppressMarkers = false;
		}

		var polylineOptions ={ strokeColor: _colour, strokeWeight: _strokeWeight, strokeOpacity: _strokeOpacity  };

		var rendererOptions = {draggable: false, suppressMarkers: _suppressMarkers, polylineOptions: polylineOptions};

		return rendererOptions;
	}

	function renderDirections(result, rendererOptions, routeToDisplay)
	{

		if(routeToDisplay==0)
		{
			var _colour = '#00458E';
			var _strokeWeight = 4;
			var _strokeOpacity = 1.0;
			var _suppressMarkers = false;
		}
		else
		{
			var _colour = '#ED1C24';
			var _strokeWeight = 4;
			var _strokeOpacity = 0.7;
			var _suppressMarkers = false;
		}

		var directionsRenderer = new google.maps.DirectionsRenderer({
			draggable: false,
			suppressMarkers: _suppressMarkers,
			polylineOptions: {
				strokeColor: _colour,
				strokeWeight: _strokeWeight,
				strokeOpacity: _strokeOpacity
			}
		});
		directionsRenderer.setMap(map);
		directionsRenderer.setPanel(document.getElementById('directions_panel'));
		directionsRenderer.setDirections(result);
		directionsRenderer.setRouteIndex(routeToDisplay);
	}

	function requestDirections(start, end, routeToDisplay, main_route) {

		var request = {
			origin: start,
			destination: end,
			travelMode: google.maps.DirectionsTravelMode.DRIVING,
			provideRouteAlternatives: main_route
		};


		directionsService.route(request, function(result, status) {
			if (status == google.maps.DirectionsStatus.OK)
			{
				if(main_route)
				{
					var rendererOptions = getRendererOptions(true);
					for (var i = 0; i < result.routes.length; i++)
					{
						renderDirections(result, rendererOptions, i);
					}
				}
				else
				{
					var rendererOptions = getRendererOptions(false);
					renderDirections(result, rendererOptions, routeToDisplay);
				}
			}
		});
	}


	function calcRoute() {

		directionsService.route(request, function(result, status) {
			if (status == google.maps.DirectionsStatus.OK) {
				directionsDisplay.setDirections(result);
				var route   = result.routes[0];
				var total   = 0;
				var time    = 0;
				var summaryPanel = document.getElementById("total");
				summaryPanel.innerHTML = '';
				for (var i = 0; i < route.legs.length; i++) {

					var routeSegment = i + 1;

					summaryPanel.innerHTML  += '<h2>Routen Abschnitt ' + routeSegment + ':</h2>';
					summaryPanel.innerHTML  += '<p>Von: ' + route.legs[i].start_address + '</p>';
					summaryPanel.innerHTML  += '<p>Nach: ' + route.legs[i].end_address + '</p>>';
					summaryPanel.innerHTML  += '<p>Distanz: ' + route.legs[i].distance.text + '</p>';
					summaryPanel.innerHTML  += '<p>Zeit: ' + route.legs[i].duration.text + '</p>';
					total                   += route.legs[i].distance.value;
					time                    += route.legs[i].duration.value;
				}
				total = total / 1000.0;
				summaryPanel.innerHTML += '<p>Gesamt Kilometer: '+ total.toPrecision(4) + ' km</p><br>';
				time = time * 1000.0;
				var msecPerMinute = 1000 * 60;
				var msecPerHour = msecPerMinute * 60;
				var msecPerDay = msecPerHour * 24;
				var interval = time;
				var tage = Math.floor(interval / msecPerDay );
				interval = interval - (tage * msecPerDay );
				var stunden = Math.floor(interval / msecPerHour );
				interval = interval - (stunden * msecPerHour );
				var minuten = Math.floor(interval / msecPerMinute );
				interval = interval - (minuten * msecPerMinute );
				var sekunden = Math.floor(interval / 1000 );
				summaryPanel.innerHTML += '<p>Gesamt Zeit: ';
				if(tage!='')
				{
					summaryPanel.innerHTML += tage + ' Tage, ';
				}
				if(stunden!='')
				{
					summaryPanel.innerHTML += stunden + ' Stunden, ';
				}
				if(minuten!='')
				{
					summaryPanel.innerHTML += minuten +' Minuten, ';
				}
				if(sekunden!='')
				{
					summaryPanel.innerHTML += sekunden +' Sekunden </p>';
				}
			}
		});
	}
	google.maps.event.addDomListener(window, 'load', initialize);
</script>


