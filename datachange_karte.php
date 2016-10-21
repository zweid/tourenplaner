<?php

include("../../GeneratedItems/config.php");
$checkrecht="touren";
include("../rechtecheck.php");
include("../../GeneratedItems/debug.php");
include("../standard/funktionen.tri_private.php");
include("../allgfunktionen.php");
include("funktionen.php");
if(modulvorhanden('mandanten') == TRUE)
{
	require_once("../mandanten/funktionen.php");
}
if(modulvorhanden('kunden') == TRUE)
{
	require_once("../kunden/funktionen.php");
}
if(modulvorhanden('produkte') == TRUE)
{
	require_once("../produkte/funktionen.php");
}



if($sucheloeschen==1)
{
	trieinstellungsetzen("touren",$edit,'uebersicht_plz','');
	trieinstellungsetzen("touren",$edit,'uebersicht_radius','');
	trieinstellungsetzen("touren",$edit,'touranzeige','');
	$hinweisausgabe_plz = 'true';
}
elseif($sucheaktiv==1)
{
	trieinstellungsetzen("touren",$edit,'uebersicht_plz',(int) $plz);
	trieinstellungsetzen("touren",$edit,'uebersicht_radius',(int) $radius);
	trieinstellungsetzen("touren",$edit,'touranzeige',(int) $touranzeige);
}
else
{
	$plz			    = (int) trieinstellungauslesen("touren",$edit,'uebersicht_plz');
	$radius			    = (int) trieinstellungauslesen("touren",$edit,'uebersicht_radius');
	$touranzeige	    = (int) trieinstellungauslesen("touren",$edit,'touranzeige');
	$hinweisausgabe_plz = '';
};

if($aktionvorhanden=="1")
{
	if($aktion == "1")
	{

		if($tour>0)
		{
			$kunde = 'false';
			$sql_touren_kunden = "SELECT ID FROM kunden ";
			$res_touren_kunden = tri_db_query($datebanknamecms, $sql_touren_kunden);
			while($row_touren_kunden = mysql_fetch_assoc($res_touren_kunden))
			{

				if( ${"objekt_". $row_touren_kunden['ID']} == 1)
				{
					$insert_daten = $row_touren_kunden;
					unset($insert_daten['ID']);
					unset($kunde);
					if($speichern == '1') {
						$sql_kunde_exists = "SELECT kundennummer, count(kundennummer) AS count FROM touren_kunden_zuordnung WHERE kundennummer=" . $row_touren_kunden['ID'] . " AND tour=" . $tour . " ";
						$res_kunde_exists = tri_db_query($datenbanknamecms, $sql_kunde_exists);
						$row_kunde_exists = mysql_fetch_assoc($res_kunde_exists);
						pre($row_kunde_exists);
						if($row_kunde_exists['count'] != 0)
						{
							$hinweisausgabe_tour .= 'Der Kunde ' . kunden_verlinkung($row_touren_kunden['ID']) . ' wurde bereits der Tour zugeordnet<br/>';
						}
						else
						{
							$hinweisausgabe_tour .= 'Der Kunde ' . kunden_verlinkung($row_touren_kunden['ID']) . ' wurde erfolgreich zur Tour hinzugef&uuml;gt<br/>';
							$sql_speichern = "	INSERT INTO 	touren_kunden_zuordnung
												SET 		    kundennummer 	= $row_touren_kunden[ID],
																tour        	= $tour ";
							tri_db_query($datenbanknamecms, $sql_speichern);
						}
					}
				}
			}
			if($kunde == 'false')
			{
				$hinweisausgabe_tour = 'Sie haben keine Tour Ausgew&auml;hlt oder keine Kunden markiert.';
			}
		}
		else
		{
			$hinweisausgabe_tour = 'Sie haben keine Tour Ausgew&auml;hlt oder keine Kunden markiert.';
		}
	}

	if($sucheaktiv=="1")
	{
		$koordinaten_string = "";

		// PLZ und Umkreis vorhanden?
		if($plz!='' && $radius>0)
		{

			$kunden_umkreissuche	= kunden_umkreissuche($plz,$radius);
			$update = touren_geo_koordinaten_update($plz);
			$js_koords				= array();
			foreach($kunden_umkreissuche as $key => $value)
			{
				$land		 	= kunden_datenfelder($value['kundennummer'],'landesfeld');
				$strasse	    = kunden_datenfelder($value['kundennummer'],'strassenfeld');
				$plz		    = $value['wert1'];
				$ort		    = kunden_datenfelder($value['kundennummer'],'ortsfeld');
				$koordinaten    = touren_geo_google_koordinaten($strasse,$plz,$ort);
				if(trim($koordinaten)!="" && trim($koordinaten)!=",")
				{
					$koords     = "['<br><strong>Kd-Nr.: ".$value['kundennummer']."</strong><br><a href=\"javascript:addCustomer(".$value['kundennummer'].")\" class=\"addCustomer\">".kunden_namenausgabe($value['kundennummer'])." hinzuf&uuml;gen</a>',".$koordinaten."]";
					array_push($js_koords, $koords);
				}
				$auswahl[$value['kundennummer']]    = array('kundennummer'  => $value['kundennummer'],
					'tour'          => 'null',
					'jskoords'      => $koords);
			}
			$koordinaten_suche .= implode(', ', $js_koords);
		}
		if($touranzeige>0)
		{
			$kundentour         = array();
			$sql_tourenanzeige  = "SELECT * FROM touren_kunden_zuordnung WHERE tour=$touranzeige ";
			$res_tourenanzeige  = tri_db_query($GLOBALS['datenbanknamecms'], $sql_tourenanzeige);
			while($row_tourenanzeige = mysql_fetch_assoc($res_tourenanzeige))
			{
				$strasse	    = kunden_datenfelder($row_tourenanzeige['kundennummer'],'strassenfeld');
				$plz		    = kunden_datenfelder($row_tourenanzeige['kundennummer'],'plzfeld');
				$ort		    = kunden_datenfelder($row_tourenanzeige['kundennummer'],'ortsfeld');
				$koordinaten    = touren_geo_google_koordinaten($strasse,$plz,$ort);
				if(trim($koordinaten)!="" && trim($koordinaten)!=",")
				{
					$koords     = "['<br><strong>Kd-Nr.: ".$row_tourenanzeige['kundennummer']."</strong><br><a href=\"javascript:addCustomer(".$row_tourenanzeige['kundennummer'].")\" class=\"addCustomer\">".kunden_namenausgabe($row_tourenanzeige['kundennummer'])." hinzuf&uuml;gen</a>',".$koordinaten."]";
					array_push($js_koords, $koords);
				}
				$auswahl[$row_tourenanzeige['kundennummer']]    = array(    'kundennummer'  => $row_tourenanzeige['kundennummer'],
					'tour'          => $touranzeige,
					'jskoords'      => $koords);
			}
			$koordinaten_tour = implode(', ', $js_koords);
		}

		$koordinaten_string = $koordinaten_suche.$koordinaten_tour;
		if($koordinaten_string!='')
		{
			$google_api = trieinstellungauslesen('administration','system','google_api' );
			if(trim($google_api)=='')
			{
				$google_api		= 'AIzaSyBmsl7HIjIoZD-lM6gcH6YN-Hpcrlff5DA';
			}
			$google_maps    = '<script type="text/javascript"
						      src="http://maps.googleapis.com/maps/api/js?key='.$google_api.'&sensor=false">
						    </script>';
			foreach($auswahl as $key => $value)
			{
				$test   = $auswahl[$key];
				$jskoords_string .= $value['jskoords'].',';
			}
		}
		else
		{
			$hinweisausgabe_plz = 'true';
		}
	}
}




if($hinweisausgabe_plz=='true')
{
	$hinweis    = 'Geben Sie eine Postleitzahl und einen Umkreis an, klicken Sie anschlie&szlig;end auf "Suchen" um die Karte angezeigt zu bekommen.';
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
	<title>Karte</title>
		<style type="text/css">
			#map_canvas {
				border: none;
				background: none;
				width: 100%;
				height: 600px;
			}
		</style>
	<?php echo tri_header_includes(); echo $google_maps; ?>
</head>
<body class="frame_bg_data" onload="initialize()">
<h1>Karte</h1>
	<form id="suchformular" action="datachange_karte.php" method="post" name="suchformular">
		<div class="tri_box_datachange">
			<div class="content">
				<table border="0" cellspacing="0" cellpadding="2" align="center">
					<tr>
						<td rowspan="3" align="left" valign="top" width="50"><br>
							<img src="../images/32x32/view_sh.png" alt="" height="32" width="32" border="0">
						</td>
						<td align="left"    width="120">PLZ:</td>
						<td align="right"   width="160">
							<input type="text" name="plz" value="<?php echo $plz; ?>" size="17" class="Feld"/>
						</td>
						<td align="left"    width="20"></td>
						<td align="left"    width="120">Umkreis:</td>
						<td align="right"   width="160" >
							<input type="text" name="radius" value="<?php echo $radius; ?>" size="25" class="Feld"/>
						</td>
					</tr>
					<tr>
						<td align="left"    >Tour anzeigen:</td>
						<td align="right"   >
							<select class="Feld" name="touranzeige" size="1">
								<option value="" <?php if($touranzeige == "" || $touranzeige == 0) {echo 'selected';}; ?>>Keine Auswahl</option>
								<?php echo touren_auswahl($touranzeige); ?>
							</select>
						</td>
						<td align="left"    width=""></td>
						<td align="left"    width=""></td>
						<td align="right"   width="">
							<input type="hidden" name="sucheaktiv" value="1"/>
							<input type="hidden" name="aktionvorhanden" value="1"/>
							<input type="submit" name="submit" value="Suchen" class="Buttonsuchen"/>
						</td>
					</tr>
					<tr>
						<td width="160" colspan="6" align="center">
							<a href="datachange_karte.php?sucheloeschen=1">
								<img src="../images/16x16/delete2_sh.png" alt="" height="16" width="16" align="absmiddle" border="0">&nbsp;Suche zur&uuml;cksetzen</a>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</form>
	<form id="FormName2" action="datachange_karte.php" method="post" name="FormName2">
		<table width="95%" border="0" cellspacing="0" cellpadding="2" align="center">
			<tr>
				<td width="70%" >
					<?php
					if($hinweisausgabe_plz=='true')
					{
						echo hinweisausgabe('Information',$hinweis,false);
					}
					?>
					<table width="95%" border="0" cellspacing="1" cellpadding="2" bgcolor="#a1a1a1" align="center" class="tb_hover">
						<tr class="nohover">
							<td>
								<div class="tri_box_datachange" style="border: none;display: table;margin: 20px 0 auto 22px;">
									<div id="map_canvas" class="content"></div>
								</div>
							</td>
						</tr>
					</table>
				</td>
				<td width="30%" valign="top">
					<?php
					if($hinweisausgabe_tour!='')
					{
						echo hinweisausgabe('Information',$hinweisausgabe_tour,false);
					}
					if($koordinaten_suche!='')
					{


					?>
					<div class="tri_box_datachange">
						<div class="content">
							<table border="0" cellspacing="0" cellpadding="2" align="center">
								<tr>
									<td rowspan="3" align="left" valign="top" width="50"><br>
										<img src="../images/32x32/view_sh.png" alt="" height="32" width="32" border="0">
									</td>
									<td align="left" nowrap>
										Bestehende Tour
										ausw&auml;hlen: <?php echo helpdesk('touren_karte_titel', 'touren_karte_desc', 'touren'); ?>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
								</tr>
								<tr>
									<td align="left" width="">
										<select class="Feld" name="tour" size="1">
											<option value="0" <?php if($tour == ""){ echo "selected";}; ?>>Keine Auswahl</option>
												<?php echo touren_auswahl($tour); ?>
										</select>
									</td>
								</tr>
							</table>
						</div>
					</div>
					<br>
					<div id="karte_kunden_uebersicht" style="overflow:auto;max-height:450px;">
						<table width="95%" border="0" cellspacing="1" cellpadding="2" bgcolor="#a1a1a1" align="center" class="tb_hover">
							<tr class="nohover">
								<td width="21" class="datachange_row_start">&nbsp;</td>
								<td width="70" class="datachange_row">Kundennummer:</td>
								<td class="datachange_row">Kundendetails:</td>
								<td width="50" class="datachange_row_end">In Tour</td>
							</tr>
							<?php

							$color1 = "white";
							$color2 = "#ececec";
							$color = 1;

							if($auswahl != '')
							{
								$kunden_sort_array = array();
								$count = count($auswahl);
								foreach($auswahl as $key => $kunden)
								{
									$zuordnen = ($kunden['tour'] == 'null') ? '<font color="green"><b>nein</b></font>' : '<font color="red"><b>ja</b></font>';
									$kundenadresse = kunden_verlinkung($kunden['kundennummer']) . '<br>';
									$kundenadresse .= kunden_datenfelder($kunden['kundennummer'], 'strassenfeld') . '<br>';
									$kundenadresse .= kunden_datenfelder($kunden['kundennummer'], 'plzfeld') . " " . kunden_datenfelder($kunden['kundennummer'], 'ortsfeld') . '<br>';
								?>
									<tr bgcolor="<?php echo ${"color" . $color}; ?>" id="zeile_<?php echo $kunden['kundennummer']; ?>">
										<td valign="top">
											<input type="checkbox" name="objekt_<?php echo $kunden['kundennummer']; ?>" value="1">
										</td>
										<td valign="top"><?php echo $kunden['kundennummer'];?></td>
										<td valign="top"><?php echo $kundenadresse;?></td>
										<td valign="top" align="center"><?php echo $zuordnen;?></td>
									</tr>
									<?php

									$color = ($color == 1) ? $color = 2 : $color = 1;
								}
							}

						?>
						</table>
					</div>
					<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center" >
						<tr>
							<td><img src="../images/pfeil.png" alt="" height="30" width="30" border="0">
								<select name="aktion" size="1" class="Feld">
									<option value="1" <?php if($aktion==1){echo "selected";}; ?>>Auswahl speichern</option>
								</select>
								<input type="submit" name="submit" value="Ausf&uuml;hren" class="Buttonausfuehren">
								<input type="hidden" name="aktionvorhanden" value="1"/>
								<input type="hidden" name="sucheaktiv" value="1"/>
								<input type="hidden" name="plz" value="<?php echo $plz?>"/>
								<input type="hidden" name="radius" value="<?php echo $radius; ?>"/>
								<input type="hidden" name="speichern" value="1"/>
								<input type="hidden" name="touranzeige" value="<?php echo $touranzeige; ?>"/>
								<input type="hidden" name="sortieren" value="Show" onload="initialize();"/>
							</td>
							<td align="right">
								<a class="select_all">Alle w&auml;hlen</a> / <a class="deselect_all">entw&auml;hlen</a>
							</td>
						</tr>
					</table>
					<?php
					}
					?>
				</td>
			</tr>
		</table>
	</form>
</body>
</html>
<script type="text/javascript">
	// Define your locations: HTML content for the info window, latitude, longitude
function initialize() {
	var locations = [
		<?php echo $jskoords_string; ?>
	];

	// Setup the different icons and shadows
	var iconURLPrefix = 'http://maps.google.com/mapfiles/ms/icons/';

	var icons = [
		iconURLPrefix + 'red-dot.png',
		iconURLPrefix + 'green-dot.png',
		iconURLPrefix + 'blue-dot.png',
		iconURLPrefix + 'orange-dot.png',
		iconURLPrefix + 'purple-dot.png',
		iconURLPrefix + 'pink-dot.png',
		iconURLPrefix + 'yellow-dot.png'
	];
	var icons_length = icons.length;

	var shadow = {
		anchor: new google.maps.Point(49,49),
		url: iconURLPrefix + 'msmarker.shadow.png'
	};

	var map = new google.maps.Map(document.getElementById('map_canvas'), {
		zoom: 7,
		center: new google.maps.LatLng(49.8080594,9.8701047),
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		mapTypeControl: true,
		streetViewControl: false,
		panControl: false,
		zoomControlOptions: {
			position: google.maps.ControlPosition.RIGHT_TOP,
			style: google.maps.ZoomControlStyle.SMALL
		}
	});

	var infowindow = new google.maps.InfoWindow({
		maxWidth: 180
	});

	var marker;
	var markers = new Array();

	var iconCounter = 0;

	// Add the markers and infowindows to the map
	for (var i = 0; i < locations.length; i++) {
		marker = new google.maps.Marker({
			position: new google.maps.LatLng(locations[i][1], locations[i][2]),
			map: map,
			icon : icons[iconCounter],
			shadow: shadow
		});
		markers.push(marker);

		google.maps.event.addListener(marker, 'click', (function(marker, i) {
			return function() {
				infowindow.setContent(locations[i][0]);
				infowindow.open(map, marker);
			}
		})(marker, i));

		iconCounter++;
		// We only have a limited number of possible icon colors, so we may have to restart the counter
		if(iconCounter >= icons_length){
			iconCounter = 0;
		}
	}
	AutoCenter();
}
var latlngbounds = new google.maps.LatLngBounds();
for (var i = 0; i < latlng.length; i++) {
	latlngbounds.extend(latlng[i]);
}
function AutoCenter() {
	//  Create a new viewpoint bound
	var bounds = new google.maps.LatLngBounds();
	//  Go through each...
	$.each(markers, function (index, marker) {
		bounds.extend(marker.position);
	});
	//  Fit these bounds to the map
	map.fitBounds(bounds);
}
function addCustomer(kundennummer)
{
	document.forms["FormName2"].elements["objekt_"+kundennummer].click();
}
</script>
<?php
function touren_geo_koordinaten_update($suche)
{
	$res_plz = tri_db_query($datenbanknamecms, "select ID from kunden");
	while($row_plz = mysql_fetch_assoc($res_plz))
	{
		//pre($row_plz,4);
		foreach($row_plz as $plz_key => $plz_val)
		{
			$plz		            = kunden_datenfelder($row_plz['ID'],'plzfeld');
			$land		            = kunden_datenfelder($row_plz['ID'],'landesfeld',2);
			if(!in_array($plz,$plz_array))
			{
				$plz_array[$plz] =  array(plz => $plz,land => $land);
			}
		}

		$sql_plz            .= " plz='$plz' or ";
		$sql_plz2           .= " plz2='$plz' or ";
		$sql_geo_select      = "Select plz,plz2 From meinedelikatessen.tri_geo_erweitert WHERE (".$sql_plz.") ";
		$sql_geo_select      = str_ireplace('or )',' )',$sql_geo_select);
		$sql_geo_select     .= " AND (".$sql_plz2.") ";
		$sql_geo_select      = str_ireplace('or )',' )',$sql_geo_select);
		$res_geo_erweitert    = tri_db_query($datenbanknamecms,$sql_geo_select);
		while($row_geo_erweitert = mysql_fetch_assoc($res_geo_erweitert))
		{
			foreach($row_geo_erweitert as $statment => $statment_val)
			{
				$sql        =      "Select plz,plz2 From tri_geo_erweitert where plz=".$row_geo_erweitert['plz']." AND plz2=".$row_geo_erweitert['plz2']." ";
				//pre($sql,2);
				$res_ort    = tri_db_query($datenbanknamecms,$sql);
				if(mysql_num_rows($res_ort)==0)
				{
					$entfernung1  = tri_geo_entfernung($suche,$row_geo_erweitert['plz2'],'DE');
					$insert_geo1 = "INSERT INTO  tri_geo_erweitert set plz='$suche',plz2='$row_geo_erweitert[plz2]',land='DE',entfernung='$entfernung1'";


					tri_db_query($GLOBALS['datenbanknamecms'],$insert_geo1);
				}
				$sql_plz_2  =      "Select plz,plz2 From tri_geo_erweitert where plz=".$row_geo_erweitert['plz2']." AND plz2=".$suche." ";
				$res_ort2   = tri_db_query($datenbanknamecms,$sql_plz_2);
				if(mysql_num_rows($res_ort2)==0)
				{
					$entfernung2    = tri_geo_entfernung($row_geo_erweitert['plz2'],$suche,'DE');
					$insert_geo2    = "INSERT INTO  tri_geo_erweitert set plz='$row_geo_erweitert[plz2]',plz2='$suche',land='DE',entfernung='$entfernung2' ";

				//	pre($insert_geo2,2);
					tri_db_query($GLOBALS['datenbanknamecms'],$insert_geo2);
				}

			}

		}
	}
}
?>
