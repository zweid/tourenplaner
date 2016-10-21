<?php

include("../../GeneratedItems/config.php");
$checkrecht="touren";
include("../rechtecheck.php");
include("../../GeneratedItems/debug.php");
include("../allgfunktionen.php");
include("../kunden/funktionen.php");
include("funktionen.php");

$sql_tourenanzeige  = "SELECT * FROM touren_kunden_zuordnung WHERE tour=8 AND tourplanen=1 ORDER BY prio ASC ";
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


<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no" />
	<title>ConnectOriginsToDestinations</title>
	<link rel="stylesheet" href="//js.arcgis.com/3.13/dijit/themes/claro/claro.css" />
	<link rel="stylesheet" href="//js.arcgis.com/3.13/esri/css/esri.css" />
	<script src="js/dojo.js"></script>
	<style>
	 html, body, #border-container
		{
			height: 100%;
			margin: 0;
		}
		/* Don't display the analysis widget's close icon*/
		.esriAnalysis .esriAnalysisCloseIcon
		{
			display: none;
		}
	</style>
	<script src="http://js.arcgis.com/3.13/"></script>
	<script>
	 require([
		 "dojo/ready",
		 "dojo/parser",
		 "esri/urlUtils",
		 "esri/map",
		 "esri/layers/FeatureLayer",
		 "esri/dijit/analysis/ConnectOriginsToDestinations",
		 "esri/InfoTemplate",
		 "dijit/layout/BorderContainer",
		 "dijit/layout/ContentPane",
		 "dojo/domReady!"
	 ], function (
			ready,
			parser,
			urlUtils,
			Map,
			FeatureLayer,
			ConnectOriginsToDestinations,
			InfoTemplate
		) {
			ready(function() {
				parser.parse();

				urlUtils.addProxyRule({
					urlPrefix: "route.arcgis.com",
					proxyUrl: "/sproxy/"
				});

				var map = new Map("map", {
					basemap: "topo",
					center: [-117.76,34.06],
					zoom: 10
				});





var originsLayer = <?php echo $js_start; ?>;
var destinationsLayer = <?php echo $js_end; ?>;

				destinationsLayer	= new FeatureLayer ({
name: "destinations",
outFields: ["*"]
});
map.addLayers([originsLayer, destinationsLayer]);

var analysisTool;

map.on("layers-add-result", initializeTool);
function initializeTool() {
var params = {};
params.portalUrl = "http://www.arcgis.com";
params.originsLayer = originsLayer;
params.featureLayers = [destinationsLayer];
params.map = map;
params.distanceDefaultUnits = "Miles";
params.returnFeatureCollection = true;

analysisTool = new ConnectOriginsToDestinations(params, "toolPane");
analysisTool.startup();

analysisTool.on("job-result", function (result) {
var resultLayer = new FeatureLayer(result.value.url || result.value, {
outFields: ['*'],
infoTemplate: new InfoTemplate()
});
map.addLayer(resultLayer);

});
}
});
});
</script>
</head>
<body class="claro">
<div id="border-container" data-dojo-type="dijit/layout/BorderContainer" data-dojo-props="design:'headline',gutters:false">
<div id="map" data-dojo-type="dijit/layout/ContentPane" data-dojo-props="region:'center'"
style="padding: 0;">
</div>
<div data-dojo-type="dijit/layout/ContentPane" data-dojo-props="region:'left'" style="width: 300px;">
<div id="toolPane">
</div>
</div>
</div>
</body>
</html>

<!--<div class="container">

#########Box Slide Out Menu ##############


	<div class="profile">
		<div class="avatar"><img src="" /></div>
		<div class="follow"><button><i class="icon-plus"></i> Follow</button></div>
	</div>
	<div class="profile-container unfold">
		<ul class="profile-list">
			<li class="first"><i class="icon-user"></i> Profile</li>
			<li class="second"><i class="icon-list-alt"></i> Activity</li>
			<li class="third"><i class="icon-time"></i> Timeline</li>
			<li class="fourth"><i class="icon-heart"></i> Favorites</li>
		</ul>
	</div>
</div>-->


