<?php
	include("../../GeneratedItems/config.php");
	$checkrecht="touren";
	include("../rechtecheck.php");
	include("../../GeneratedItems/debug.php");
	include("../allgfunktionen.php");
	include("../mailer.php");
	include("../kunden/funktionen.php");
	include("../standard/funktionen.php");
	include("funktionen.php");

	if (modulvorhanden('dateisystem')==TRUE)
	{
		require_once('../dateisystem/funktionen.php');
	}
	
	$warnmeldung		= '';
	$hinweisausgabe		= '';
	
	if($produkt_eintragen==1)
	{
		if(touren_kunden_eintragen($kundenid, $ID)==true)
		{
			$hinweisausgabe = 'Der ausgew&auml;hlte Kunde befindet sich bereits in der Touren&uuml;bersicht und kann nicht mehr als einmal hinzugef&uuml;gt werden.';
		};
	};

	if($update==1)
	{
		$start		= $jahr_start.'-'.$monat_start.'-'.$tag_start;
		$ende		= $jahr_ende.'-'.$monat_ende.'-'.$tag_ende;
		if(datumnachtimestamp($ende)<datumnachtimestamp($start))
		{
			$hinweisausgabe = 'Das Startdatum liegt vor dem Ende.';
			$ende 	= $start;
		}
		$sql = "UPDATE touren SET tourende='$ende', tourstart='$start', titel='$titel', anmerkung_intern='".$anmerkung_intern."', status='".$status."', zustaendig='".$zustaendig."' WHERE touren.ID='$ID' ;";
		tri_db_query ($datenbanknamecms, $sql);
	};

	if($aktionvorhanden=="1")
	{
		if($aktion=="1")
		{	
			$sql = "SELECT kundennummer FROM touren_kunden_zuordnung WHERE tour='$ID'";
			$res = tri_db_query ($GLOBALS['datenbanknamecms'], $sql);
			while ($row = mysql_fetch_array ($res)){
				$prio				= ${'prio_'.$row['kundennummer']};
				$kundenid			= ${'kundenid_'.$row['kundennummer']};
				$tourplanen         = ${'tourplanen_'.$row['kundennummer']};
				$datum_termin_start = ${'jahr_datum_termin_start_'.$row['kundennummer']}.'-'.${'monat_datum_termin_start_'.$row['kundennummer']}.'-'.${'tag_datum_termin_start_'.$row['kundennummer']};
				$datum_termin_ende 	= ${'jahr_datum_termin_ende_'.$row['kundennummer']}.'-'.${'monat_datum_termin_ende_'.$row['kundennummer']}.'-'.${'tag_datum_termin_ende_'.$row['kundennummer']};

				$sql_tour_vonbis 	= 'SELECT tourstart, tourende FROM touren WHERE ID='.$_POST['ID'].'';
				$res_tour_vonbis 	= tri_db_query($datenbanknamecms,$sql_tour_vonbis);
				$row_tour_vonbis	= mysql_fetch_array ($res_tour_vonbis);
				$start 	= $row_tour_vonbis['tourstart'];
				$ende	= $row_tour_vonbis['tourende'];
				
				if(datumnachtimestamp($datum_termin_start)<datumnachtimestamp($start) || datumnachtimestamp($datum_termin_ende)>datumnachtimestamp($ende) || datumnachtimestamp($datum_termin_ende)<datumnachtimestamp($datum_termin_start))
				{
					if(datumnachtimestamp($datum_termin_start)>datumnachtimestamp($start))
					{
						$kundenlink = kunden_verlinkung($row['kundennummer']);
						$kundenlink_temp .= $kundenlink.' Startdatum angepasst.<br>';
						$datum_termin_start  = $start;
					}
					if(datumnachtimestamp($datum_termin_ende)>datumnachtimestamp($ende))
					{
						$kundenlink = kunden_verlinkung($row['kundennummer']);
						$kundenlink_temp .= $kundenlink.' Enddatum angepasst.<br>';
						$datum_termin_ende  = $ende;
					}			
					
					if(datumnachtimestamp($datum_termin_ende)<datumnachtimestamp($datum_termin_start))
					{
						$kundenlink = kunden_verlinkung($row['kundennummer']);
						$kundenlink_temp .= $kundenlink.' Das Enddatum lag vor dem Startdatum.<br>';
						$datum_termin_ende 	= $datum_termin_start;
					}
					$hinweisausgabe = 'Das Datum bei folgenden Kunden war nicht korrekt<br>und wurde deshalb automatisch angepasst:<br>'.$kundenlink_temp;
				}
				if($tourplanen=='')
				{
					$tourplanen = 0;
				}
				tri_db_query ($datenbanknamecms, 
					"	UPDATE	
							touren_kunden_zuordnung 
						SET 	
							anmerkung			= '".string2XHTML(${'anmerkung_'.$row['kundennummer']})."',
							prio		        = '".$prio."',
							datum_termin_start	= '".$datum_termin_start."', 
							datum_termin_ende	= '".$datum_termin_ende."',
							tourplanen          = '".$tourplanen."'
						WHERE	
							kundennummer = '".$kundenid."' 
						AND 
							tour 		= '".$_POST['ID']."'
						;"
				);
			}	
		}
		
		if($aktion=="2"){
			$sql = "SELECT kundennummer FROM touren_kunden_zuordnung WHERE tour='$ID'";
			$res = tri_db_query ($GLOBALS['datenbanknamecms'], $sql);
			while ($row = mysql_fetch_array ($res)){
				if(${"objekt_".$row['kundennummer']}==1){
					tri_db_query ($datenbanknamecms, "delete from touren_kunden_zuordnung where kundennummer='$row[kundennummer]'");				
				}
			}
		}
	};
	
	if($generieren==1)
	{
		if ($alternativ_template_hf<>"")
		{
			trieinstellungsetzen("touren","system","alternativ_template_hf",$alternativ_template_hf);
			$template_hf= $alternativ_template_hf;
		}
		else
		{
			trieinstellungsetzen("touren","system","alternativ_template_hf",'');
		}
		$template_typ 	= "templatemitlogo";
		
		if ($alternativ_template<>"")
		{
			$template_typ 	= "alternativ_template";
			trieinstellungsetzen("touren","system",$template_typ.$mandant,$alternativ_template);
			$template 		= $alternativ_template;
		}
		else
		{
			trieinstellungsetzen("touren","system","alternativ_template",'');
		}
		touren_generieren($ID,$template,'',$template_typ,$template_hf);
		tri_db_query ($datenbanknamecms, "UPDATE touren set generiert='$generieren',generiert_von='$edit',generiert_am='$datum' where ID='$ID'");
		logfile('touren','touren',$ID,'ID',2);
	}
	
	$arr_touren_kunden 	= touren_kunden_array($ID);
	$res_touren 		= tri_db_query ($datenbanknamecms, "SELECT * FROM touren where ID='$ID'");
	$row_touren 		= mysql_fetch_array ($res_touren);
	
	$touren_posten			= array();
	$sql				= "SELECT * FROM touren, touren_kunden_zuordnung WHERE ID='$ID' AND touren.ID=touren_kunden_zuordnung.tour order by prio asc";		
	$res 				= tri_db_query ($datenbanknamecms, "$sql");
	while ($row 		= mysql_fetch_array ($res))
	{
		array_push($touren_posten, $row);								
	}
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
	<title>touren</title>
	<?php  echo tri_header_includes(); ?>
</head>
<body class="frame_bg_data">
	<h1>Touren <?php echo $ID; ?> - <?php echo strip_tags($row_touren['titel'])?><a href="datachange.php" class="backlink">Zur Touren&uuml;bersicht</a></h1>

<?php 
	
	if($fehlerausgabe<>'')
	{ 
		echo fehlerausgabe_detail('Achtung',$fehlerausgabe,false);
	}
?>
	<div class="tri_box_status">
		<div class="content">
			<table border="0" cellspacing="0" cellpadding="0" align="center">
				<?php if(trieinstellungauslesen("administration","system","status_icons")==1){?>
				<tr>
					<td align="center" align="center" width="140"><img src="../images/32x32/document_add_sh.png" alt="" height="32" width="32" border="0"></td>
					<td align="center" width="40"></td>
					<td align="center" width="140" align="center"><img src="../images/32x32/document_edit_sh.png" alt="" height="32" width="32" border="0"></td>
					<td align="center" width="40"></td>
					<td align="center" width="140" align="center"><img src="../images/32x32/document_exchange_sh.png" alt="" height="32" width="32" border="0"></td>
					<td align="center" width="40"></td>
					<td align="center" width="140" align="center"><img src="../images/32x32/document_exchange_sh.png" alt="" height="32" width="32" border="0"></td>
				</tr>
				<?php } ?>
				<tr height="35">
					<td align="center" width="140" height="35"><strong>1. Tour anlegen</strong></td>
					<td align="center" width="40" height="35"></td>
					<td align="center" width="140" height="35"><strong>2. Tour anpassen</strong></td>
					<td align="center" width="40" height="35"></td>
					<td align="center" width="140" height="35"><strong>3. Tour generieren</strong></td>
					<td align="center" width="40" height="35"></td>
						<td align="center" width="140" height="35"><strong>3. Tour beendet</strong></td>
					</tr>
				<tr>
						<td colspan="7" align="center" width="500">
						<hr class="trenner">
					</td>
					</tr>
				<tr>
					<td align="center" align="center" width="140"><img src="../images/16x16/check2_sh.png" alt="" height="16" width="16" border="0"></td>
					<td align="center" width="40"><img src="../images/16x16/arrow_right_blue_sh.png" alt="" height="16" width="16" border="0"></td>
					<td align="center" width="140">
					<?php 
						if(touren_status_pruefen($row_touren['status']) || count($touren_posten)>0)
						{ 
							$anpassen=1;
							echo "<img src=\"../images/16x16/check2_sh.png\" height=\"16\" width=\"16\" border=\"0\">";
						}
						else
						{
							echo "<img src=\"../images/16x16/delete2_sh.png\" height=\"16\" width=\"16\" border=\"0\">";
						}; 
					?></td>
						<td align="center" width="40"><img src="../images/16x16/arrow_right_blue_sh.png" alt="" height="16" width="16" border="0"></td>
						<td width="150" align="center" height="16">
						<?php 
							if($row_touren['generiert']==1 or $row_touren['generiert']==2)
							{ 
								echo "<img src=\"../images/16x16/check2_sh.png\" height=\"16\" width=\"16\" border=\"0\">";
								$generiert=1;
							}else{
								echo "<img src=\"../images/16x16/delete2_sh.png\" height=\"16\" width=\"16\" border=\"0\">";
							}; 
						?>
					</td>
					<td align="center" width="40"><img src="../images/16x16/arrow_right_blue_sh.png" alt="" height="16" width="16" border="0"></td>
						<td align="center" width="140">
					<?php 
						if(touren_status_pruefen($row_touren['status']))
						{ 
							echo "<img src=\"../images/16x16/check2_sh.png\" height=\"16\" width=\"16\" border=\"0\">";
						}
						else
						{
							echo "<img src=\"../images/16x16/delete2_sh.png\" height=\"16\" width=\"16\" border=\"0\">";
						}; 
					?>
					</td></tr>
				<tr>
						<td colspan="7" align="center" width="500">
						<hr class="trenner">
					</td>
					</tr>
				<tr>
					<td align="center" width="140"></td>
					<td align="center" width="40"></td>
						<td width="150" align="center">
					<?php 
						$res_anzahl_posten = tri_db_query ($datenbanknamecms, "SELECT kundennummer FROM touren_kunden_zuordnung where tour='$ID' limit 1");
						$anzahl_posten = mysql_num_rows($res_anzahl_posten);
						if($anzahl_posten==0)
						{ 
							echo "<font color=red>Sie m&uuml;ssen der Tour Kunden hinzuf&uuml;gen</font>";
						}; 
					?>
					</td>
						<td align="center" width="40"></td>
						<td width="150" align="center">
						<?php
							 
							if($anpassen==1 && $row_touren['generiert']==0)
							{
								echo "<a href=\"zeigedaten.php?ID=$ID&generieren=1\">generieren</a>";
							}
							elseif($row_touren['generiert']==1 or $row_touren['generiert']==2)
							{
								$keycode=md5(md5($row_touren['generiert_am']).md5($row_touren['datum']).md5($row_touren['kundennummer']));
								echo "<a href=\"/modul.php?modul=touren&modulkat=fileopen&keycode=$keycode&ID=$ID\" target=_blank>&ouml;ffnen</a>";
							};
							
						?>
					</td></tr>
				<tr>
					<td align="center" width="140"></td>
					<td align="center" width="40"></td>
					<td align="center" width="140"><br>
						</td>
					<td align="center" width="40"></td>
						<td width="150" align="center">
						<?php 
							
							if($row_touren['generiert']==1 || $row_touren['generiert']==2)
							{ 
								$pfad="tourentemplate/";
						?>
						<form id="generate" action="zeigedaten.php" method="get" name="generate">
							<img src="../images/24x24/document_gear_sh.png" alt="Templateauswahl" title="Hier k&ouml;nnen Sie die Templates ausw&auml;hlen" height="18" width="18" align="absmiddle" border="0" class="showthis" elem="#template_config">
							<div class="tri_tooltip" id="template_config" style="display:none;">
								<div>
									<img src="../images/trans.gif" alt="" border="0" class="close"><strong class="headline">Templateauswahl:</strong><br/><br/>
									<table width="100%" border="0" cellspacing="0" cellpadding="2">
										<tr>
											<td align="left">Inhalt:</td>
											<td align="right">
												<select name="alternativ_template" size="1" class="Feld">
													<option value="">Standardtemplate</option>
													<?php echo standard_template_auswahl($pfad,$alternativ_template); ?>
												</select>
											</td>
										</tr>
										<tr>
											<td align="left">Header/Footer:</td>
											<td align="right">
												<select name="alternativ_template_hf" size="1" class="Feld">
													<option value="">Standardtemplate</option>
													<?php echo standard_template_auswahl("../standard/standardtemplates/",$alternativ_template_hf); ?>
												</select>
											</td>
										</tr>
									</table>
								</div>
							</div>
							<?php echo "<a href=\"javascript:document.generate.submit();\" >neu generieren</a><br/>";?>
							<input type="hidden" name="ID" value="<?php echo $ID; ?>">
							<input type="hidden" name="generieren" value="1">
						</form>
						<?php } ?>
					</td>
					</tr>
			</table>
		</div>
	</div>
	<form id="einstellungen" action="zeigedaten.php" method="post" name="einstellungen">
		<div class="tri_box">
			<p>Touren bearbeiten:</p>
			<div class="content">
				<table border="0" cellspacing="0" cellpadding="2" align="center">
					<tr>
						<td rowspan="7" align="center" valign="top" width="100"><br/>
							<img src="../images/32x32/document_edit_sh.png" alt="Bearbeiten" title="Bearbeiten" height="32" width="32" border="0"><br>
							<br>
							<?php
								if(function_exists('tri_wiedervorlage_button'))
								{
									echo tri_wiedervorlage_button('touren','touren',$ID,'/cmssystem/touren/zeigedaten.php?ID='.$ID);
								}
							?>
						</td>
						<td width="150">Status:</td>
						<td><select class="Feld" name="status" size="1" >
								<?php echo touren_status_auswahl($row_touren['status']); ?>
							</select></td>
						<td width="25"></td>
						<td width="150">Angelegt von / am:</td>
						<td align="right" nowrap>
						<?php
							if(function_exists('tri_sachbearbeiter_auswahl') && $edit_editieren==1)
							{
								echo '<select class="Feld" name="sachbearbeiter" size="1">'.tri_sachbearbeiter_auswahl('bestellungen',$row_touren['edit']).'</select>';
							}
							else
							{
								$name = tri_benutzer2realname($row_touren['edit']);
								$name = (trim($name)<>"") ? $name : $row_touren['edit'];
								echo $name;
							}
							echo " / ".datumwandeln_deutsch($row_touren['datum']);
						?>
						</td>
					</tr>
					<tr>
						<td width="150">Titel:</td>
						<td><input type="text" name="titel" value="<?php echo $row_touren['titel']; ?>" size="40" class="Feld" ></td>
						<td align="left" width="25"></td>
						<td>Zust&auml;ndig:</td>
						<td><select class="Feld" name="zustaendig" size="1"><?php echo tri_sachbearbeiter_auswahl('touren',$row_touren['zustaendig']); ?></select></td>
					</tr>
					<tr>
						<td width="150">geplanter Tourstart:</td>
						<td><?php echo datum_auswahl('einstellungen','jahr_start','monat_start','tag_start',$row_touren['tourstart'],'2006','','',2) ?></td>
						<td align="left" width="25"></td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td width="150">geplantes Tourende:</td>
						<td><?php echo datum_auswahl('einstellungen','jahr_ende','monat_ende','tag_ende',$row_touren['tourende'],'2006','','',2) ?></td>
						<td align="left" width="25"></td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td width="150">Anmerkung <font size="1">(Intern)</font>:</td>
						<td></td>
						<td width="25"></td>
						<td width="150"></td>
						<td></td>
					</tr>
					<tr>
						<td colspan="5" align="right" >
							<?php
								$editor=trieinstellungauslesen("touren","system","editor");
								if ($editor==1){
									tri_editor('anmerkung_intern',$row_touren['anmerkung_intern'],'100%','200px',2);
								} else { ?>
									<textarea name="anmerkung_intern" rows="4" cols="40" class="Feld anmerkung_intern"><?php echo XHTML2string($row_touren['anmerkung_intern']); ?></textarea>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<td colspan="5" align="center"><br/>
							<input type="hidden" name="update" value="1">
							<input type="hidden" name="ID" value="<?php echo $ID; ?>">
							<input type="submit" name="submit" value="Speichern" class="Buttonspeichern">
						</td>
					</tr>
				</table>
			</div>
		</div>
	</form>
	<br/>
	<?php
		if($hinweisausgabe<>'')
		{
			echo hinweisausgabe('Information',$hinweisausgabe,false);
		}
	?>
	<table width="95%" border="0" cellspacing="0" cellpadding="2" align="center">
		<tr>
			<td width="100%" valign="top">
				<form id="produktformular" action="zeigedaten.php" method="post" name="produktformular">
					<table width="100%" border="0" cellspacing="1" cellpadding="2" align="center" bgcolor="#a1a1a1" class="tb_hover	artikel_zeigedaten_listing">
						<tr class="nohover">
							<td width="21" class="datachange_row_start">&nbsp;</td>
							<td width="30" class="datachange_row">Pos.:</td>
							<td width="40" class="datachange_row">Prio:</td>
							<td class="datachange_row">Kunde:</td>
							<td width="100"class="datachange_row">Anmerkung:</td>
							<td width="300" class="datachange_row">Alle Anmerkungen zum Kunden:</td>
							<td width="140" class="datachange_row">Termin</td>
							<td width="21" class="datachange_row_end">Tour planen&nbsp;</td>
						</tr>
						<?php
							$color1				= "white";
							$color2				= "#dedede";
							$color				= 1;
							$maxprio			= 0;
							$result				= 0;
							foreach($touren_posten as $key => $row)
							{
								if($row['tourstart']!='')
								{
									$tourdatum_jahr_start	= substr($row['tourstart'], 0, -6);
									$tourdatum_jahr_ende	= substr($row['tourende'], 0, -6);
								}
								else
								{
									$tourdatum_jahr = '2006';
									$tourdatum_jahr_ende = '';
								}

								$result++;
								$kundenlink			= kunden_verlinkung($row['kundennummer']);
								$titel 				= explode('Kundennummer:',strip_tags($kundenlink));
								$kundennummer		= $row['kundennummer'];
								$tourplanen         = $row['tourplanen'];
								$tourplanen	        = ($row['tourplanen']==1) ? 'checked="checked"' : '';
								$kundenadresse		= '';
								$kundenadresse 		.= kunden_datenfelder($kundennummer,'strassenfeld').'<br>';
								$kundenadresse 		.= kunden_datenfelder($kundennummer,'plzfeld')." ".kunden_datenfelder($kundennummer,'ortsfeld').'<br>';
								$kundenadresse 		.= kunden_datenfelder($kundennummer,'mailfeld').'<br>';
								$kundenadresse 		.= '<b>Kundennummer: '.$kundennummer.'</b>';

								$sql_anmerkung 		= "SELECT * FROM `touren_kunden_zuordnung` WHERE kundennummer=$kundennummer";
								$res_anmerkung 		= tri_db_query ($datenbanknamecms, $sql_anmerkung);
								while($row_anmerkung = mysql_fetch_array($res_anmerkung)){
									if($row_anmerkung['anmerkung']!='' && $row_anmerkung['kundennummer']==$kundennummer)
									{
										$anmerkung[$kundennummer] .= 'Anmerkung zur Tour vom: '.datumwandeln_deutsch($row_anmerkung['datum_termin_start']).'<br><hr style="background-color:black;"><br>'.$row_anmerkung['anmerkung'].'<br><hr style="background-color:black;"><br>';
									}
								}
								echo '<tr bgcolor="'.${'color'.$color}.'">
										<td valign="top"><input type="checkbox" name="objekt_'.$row['kundennummer'].'" value="1"></td>
										<td valign="top">'.$result.'</td>
										<td valign="top"><input type="text" name="prio_'.$row['kundennummer'].'" size="2" maxlegth="3" value="'.$row['prio'].'" class="Feld"></td>
										<td valign="top"><input type="hidden" name="kundenid_'.$row['kundennummer'].'" value="'.$row['kundennummer'].'" >'.$kundenlink.'<br>'.$kundenadresse.'</td>
										<td valign="top"><textarea name="anmerkung_'.$row['kundennummer'].'" rows="4" cols="40" class="Feld">'.XHTML2string($row['anmerkung']).'</textarea>
										<td valign="top"><div style="height:100px; overflow:auto; border:1px solid #ccc; \">'.$anmerkung[$kundennummer].'</div></td>
										<td valign="top">'
											.datum_auswahl('einstellungen','jahr_datum_termin_start_'.$row['kundennummer'],'monat_datum_termin_start_'.$row['kundennummer'],'tag_datum_termin_start_'.$row['kundennummer'],$row['datum_termin_start'],$tourdatum_jahr_start-1,$tourdatum_jahr_ende+1,'',2).'<br>'
											.datum_auswahl('einstellungen','jahr_datum_termin_ende_'.$row['kundennummer'],'monat_datum_termin_ende_'.$row['kundennummer'],'tag_datum_termin_ende_'.$row['kundennummer'],$row['datum_termin_ende'],$tourdatum_jahr_start-1,$tourdatum_jahr_ende+1,'',2).
										'</td>
										<td valign="top" align="center"><input type="checkbox" name="tourplanen_'.$row['kundennummer'].'" value="1" '.$tourplanen.'></td>
									</tr>';

									$color = ($color==1) ? 2 : 1;
									if($maxprio<=$row['prio']){$maxprio=$row['prio']+1;};
								}
						?>
					</table>
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td valign="top" width="35"><img src="../images/pfeil.png" alt="" height="30" width="30" border="0"></td>
							<td>
								<select name="aktion" size="1" class="Feld">
									<option value="0">Aktion w&auml;hlen</option>
									<option value="1" <?php if($aktion==1){echo "selected";}; ?>>Aktualisieren</option>
									<option value="2" <?php if($aktion==2){echo "selected";}; ?>>L&ouml;schen</option>
								</select>
								<input type="submit" name="submit" value="Ausf&uuml;hren" class="Buttonausfuehren">
								<input type="hidden" name="ID" value="<?php echo $ID; ?>">
								<input type="hidden" name="aktionvorhanden" value="1"><br/>
							</td>
							<td align="right"><a class="select_all">Alle w&auml;hlen</a> / <a class="deselect_all">entw&auml;hlen</a></td>
						</tr>
					</table>
				</form>
				<br/>
				<table width="90%" border="0" cellspacing="0" cellpadding="0" align="center">
					<tr>
						<td valign="top" width="50%" align="left">
							<form action="zeigedaten.php#insert_produkt" method="post" name="produktform" id="produktform">
								<div class="tri_box2">
									<p>Neuen Kunden hinzuf&uuml;gen:</p>
									<div class="content">
										<table width="500" border="0" cellspacing="0" cellpadding="2" align="center">
											<tr>
												<td rowspan="3" valign="top" width="50" align="center"><br/>
													<a name="insert_produkt"><img src="../images/24x24/add2_sh.png" alt="" height="24" width="24" border="0"></a>
												</td>
												<td align="left" width="100">Kundennummer:</td>
												<td align="left">
													<table border="0" cellspacing="0" cellpadding="0">
														<tr>
															<td>
																<?php echo kunden_onkeyup_funktion('produktform','kundenid','','','&kunden_hinzufuegen_freigabe=1'); ?>
																<input type="text" name="kundenid" size="23" class="Feld" onkeyup="startsearch_kunden(this.value)" >
															</td>
															<td width="40" align="center">
																<?php echo kunden_suchenlink('produktform','kundenid','',''); ?>
															</td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td align="left" width="100">Prio</td>
												<td align="left">
													<input type="text" name="prio" size="3" value="<?php echo $maxprio; ?>" class="Feld" maxlength="3" >
												</td>
											</tr>
											<tr>
												<td align="left" width="100"><br/>
												</td>
												<td align="left"><br/>
													<input type="submit" name="submit" value="Einf&uuml;gen" class="Buttoneintragen">
													<input type="hidden" name="produkt_eintragen" value="1">
													<input type="hidden" name="ID" value="<?php echo $ID; ?>">
												</td>
											</tr>
										</table>
									</div>
								</div>
							</form>
						</td>
						<td valign="top" width="50%" align="right"></td>
					</tr>
				</table>
				<br/>
				<div class="tri_box2" align="center" style="width:100%;">
					<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#a1a1a1" class="tb_hover	artikel_zeigedaten_listing">
						<tr class="nohover">
							<td class="datachange_row_start">Tourenplaner</td>										
						</tr>
					</table>
					<table width="100%">
						<tr>
							<td width="100%">
								<iframe src="zeigedaten_karte.php?ID=<?php echo $ID;?>" frameborder="0" height="430px" width="100%"></iframe>
							</td>							
						</tr>
					</table>
				</div>
				<br>
			</td>
		</tr>
	</table>
	<br/>
</body>
</html>