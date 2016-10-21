<?php
	require_once("../../GeneratedItems/config.php");
	$checkrecht="touren";
	require_once("../rechtecheck.php");
	require_once("../../GeneratedItems/debug.php");
	require_once("../allgfunktionen.php");
	require_once("funktionen.php");
	require_once("../kunden/funktionen.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
	<title>Einstellungen</title>
	<?php  echo tri_header_includes(); ?>
</head>
<body>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%" align="center">
		<tr>
			<td valign="top" class="menu_bg">
			<?php
				$count=1;
				$bereichearray[$count]['titel']='Allgemeine Einstellungen';
				$bereichearray[$count]['icon']='../images/32x32/console_sh.png';
				$bereichearray[$count]['link']='einstellungen.php?auswahl=allgemein';
				$count++;
				$bereichearray[$count]['titel']='Tourentemplates';
				$bereichearray[$count]['icon']='../images/32x32/document_gear_sh.png';
				$bereichearray[$count]['link']='einstellungen.php?auswahl=templates';
				$count++;	
				echo tri_einstellungsmenue('Tourenplanung','',$bereichearray);
			?>
			</td>
			<td valign="top" class="frame_bg">
				<h1>Tourenplanung</h1>
<?php 

	if( $auswahl=="allgemein" || $auswahl==null)
	{
		if($speichern==1){
			trieinstellungsetzen("touren","system","editor",$editor);			
		};		
		$editor	= trieinstellungauslesen("touren","system","editor");
	?>
			<form id="FormName" action="einstellungen.php" method="post" name="FormName">
				<div class="tri_box">
					<p>Allgemeine Einstellungen:</p>
					<div class="content">
						<table border="0" cellspacing="0" cellpadding="2" align="center">
							<tr>
								<td align="left" width="350">HTML Editor aktivieren</td>
								<td align="left" width="200"><input type="checkbox" name="editor" value="1" <?php if($editor==1){echo "checked";}; ?>></td>
							</tr>
							<!--<tr>
								<td width="350">?</td>
								<td width="150"><input type="checkbox" name="aktiv" value="1" <?php if($aktiv==1){echo 'checked=checked';}; ?>></td>
							</tr>-->
							<tr>
								<td width="350"></td>
								<td width="150">
									<br><input type="submit" name="submit" value="Speichern" class="Buttonspeichern" >
									<input type="hidden" name="auswahl" value="allgemein"><input type="hidden" name="speichern" value="1">
								</td>
							</tr>
						</table>
					</div>
				</div>
			</form>
<?php
	}elseif($auswahl=="templates" ){

	if($speichern==1)
	{
		trieinstellungsetzen("touren","system","templatemitlogo",$templatemitlogo);	
		//trieinstellungsetzen("bestellungen","system","templatemitlogo_proforma",$templatemitlogo_proforma);	
	};
	$templatemitlogo			= trieinstellungauslesen("touren","system","templatemitlogo");
	//$templatemitlogo_proforma	= trieinstellungauslesen("bestellungen","system","templatemitlogo_proforma"); 
	$pfad="tourentemplate/";
	
	//echo $templatemitlogo.'<br>';	

?>
				<form id="FormName" action="einstellungen.php" method="post" name="FormName">
					<div class="tri_box">
						<p>Tourentemplates:</p>
						<div class="content">
							<table width="500" border="0" cellspacing="0" cellpadding="2" align="center">
								<tr>
									<td rowspan="3" valign="top" width="50" align="center"><br>
										<img src="../images/24x24/document_gear_sh.png" alt="" height="24" width="24" border="0"></td>
									<td width="220"><strong>Tourenvorlage:</strong></td>
									<td align="right" width="225">
										<select name="templatemitlogo" size="1" class="Feld">
											<option value="">Keine Auswahl</option>
											<?php echo standard_template_auswahl($pfad,$templatemitlogo); ?>
										</select>
									</td>
								</tr>
								<!--<tr>
									<td width="220"><strong>Proformarechnung:</strong></td>
									<td align="right" width="225">
										<select name="templatemitlogo_proforma" size="1"  class="Feld">
											<option value="">Keine Auswahl</option>
											<? echo standard_template_auswahl($pfad,$templatemitlogo_proforma); ?>
										</select>
									</td>
								</tr>-->
								<tr>
									<td valign="top" width="220"></td>
									<td align="right" width="225"><br>
										<input type="hidden" name="auswahl" value="templates">
										<input type="hidden" name="speichern" value="1">
										<input type="submit" name="submit" value="Speichern" class="Buttonspeichern">
									</td>
								</tr>
							</table>
						</div>
					</div>
				</form>
<?php 
	} 
?>
				
			</td>
		</tr>
	</table>
</body>
</html>