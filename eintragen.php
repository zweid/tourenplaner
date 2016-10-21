<?php

	include("../../GeneratedItems/config.php");
	$checkrecht="touren";
	include("../rechtecheck.php");
	include("../../GeneratedItems/debug.php");
	include("../allgfunktionen.php");
	include("../kunden/funktionen.php");
	include("../produkte/funktionen.php");
	include("../rechnungen/funktionen.php");
	include("funktionen.php");
	
	if($eintragen=="1")
	{
		if($titel!='')
		{
			$sql = "INSERT INTO touren SET 
					titel 		= '$titel', 
					anmerkung_intern 	= '$anmerkung',  
					edit		= '$edit', 
					datum		= '$datum' ;";
		
			tri_db_query ($datenbanknamecms,$sql);
			$tour = mysql_insert_id();
			
			header('Location: zeigedaten.php?ID='.$tour);			
		}
		else
		{
			$meldung = fehlerausgabe_detail('Tourenanlage fehlerhaft','Sie haben weder eine bestehende Tour ausgew&auml;hlt, noch einen Titel für eine neue Tour eingegeben! Bitte korrigieren Sie Ihre Angaben.',true);
		}
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
	<link rel="stylesheet" type="text/css" href="../GeneratedItems/style.CSS">
	<title>Tour eintragen</title>
	<script type="text/javascript" src="../standard/jquery/core/1.5/js/jquery-1.4.4.min.js"></script>
	<script type="text/javascript" src="../standard/jquery/core/1.5/js/jquery-ui-1.8.9.custom.min.js"></script>
	<script type="text/javascript" src="../standard/jquery/plugins/tri.tooltip.js"></script>
	<script src="../allgfunktionen.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="../GeneratedItems/tri_tooltip.css" />	
</head>
<body onunload="top.document.getElementById('shortsearch').style.display = 'none';" class="frame_bg_data">
	<h1>Tour eintragen<a href="datachange.php" class="backlink">Zur &Uuml;bersicht</a></h1>
	<br><?php echo $meldung; ?>
	<form id="eintragenformular" action="eintragen.php" method="post" name="eintragenformular">
		<div class="tri_box">
			<p>Neue Tour erstellen:</p>
			<div class="content">
				<table width="800" border="0" cellspacing="0" cellpadding="2" align="center">
					<tr>
						<td rowspan="3" valign="top" width="50" align="center"><br>
								<img src="../images/24x24/add2_sh.png" alt="" height="24" width="24" border="0"></td>
						<td>Titel f&uuml;r neue Tour vergeben: <?php echo  helpdesk('touren_neu_titel','touren_neu_desc','touren');?></td>
						<td>
							<input id="titel" type="text" size="20" name="titel" class="Feld">							
						</td>																							
					</tr>
					<tr>
						<td align="left">Anmerkung:</td>
						<td align="left">
							<textarea style="width: 400px;" name="anmerkung" rows="5" cols="60" class="Feld"></textarea>							
						</td>
					</tr>
					<tr>
						<td></td>
						<td valign="top"><br>
							<input type="hidden" name="eintragen" value="1">
							<input type="submit" name="submit" value="Erstellen" class="Buttoneintragen">
						</td>									
					</tr>
				</table>
			</div>
		</div>
	</form>
</body>
</html>