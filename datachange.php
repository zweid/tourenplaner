<?php

include("../../GeneratedItems/config.php");
$checkrecht="touren";
include("../rechtecheck.php");
include("../../GeneratedItems/debug.php");
include("../allgfunktionen.php");
include("../kunden/funktionen.php");
include("funktionen.php");
	
	if($aktionvorhanden=="1")
	{
		if($aktion=="1")
		{
			$res 		= tri_db_query ($datenbanknamecms, "select ID from touren");
			while ($row = mysql_fetch_array ($res))
			{
				if(${"objekt".$row['ID']}==1)
				{
					touren_loeschen($row['ID']);
				}
			}
		}
	}
	$status_array = touren_status();

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
	<title>Touren</title>
	<?php  echo tri_header_includes(); ?>
</head>
<body onunload="top.document.getElementById('shortsearch').style.display = 'none';" class="frame_bg_data">
	<h1>Touren</h1>
		<?php
			
			if($sucheloeschen==1)
			{
				trieinstellungsetzen("touren",$edit,'uebersicht_tournummer','');
				trieinstellungsetzen("touren",$edit,'uebersicht_kundennummer','');
				trieinstellungsetzen("touren",$edit,'uebersicht_anmerkung','');
				trieinstellungsetzen("touren",$edit,'uebersicht_titel','');
			}
			elseif($sucheaktiv==1)
			{
				trieinstellungsetzen("touren",$edit,'uebersicht_tournummer',(int) $tournummer);
				trieinstellungsetzen("touren",$edit,'uebersicht_kundennummer',$kundennummer);
				trieinstellungsetzen("touren",$edit,'uebersicht_anmerkung',$anmerkung);
				trieinstellungsetzen("touren",$edit,'uebersicht_titel',$titel);
			}
			else
			{
				$tournummer		= (int) trieinstellungauslesen("touren",$edit,'uebersicht_tournummer');
				$kundennummer	= trieinstellungauslesen("touren",$edit,'uebersicht_kundennummer');
				$anmerkung		= trieinstellungauslesen("touren",$edit,'uebersicht_anmerkung');
				$titel			= trieinstellungauslesen("touren",$edit,'uebersicht_titel');
			};
			
						
		?>
		<form id="suchformular" action="datachange.php" method="post" name="suchformular">
			<div class="tri_box_datachange">
				<div class="content">
					<table border="0" cellspacing="0" cellpadding="2" align="center">
						<tr>
							<td rowspan="3" align="left" valign="top" width="50"><br>
								<img src="../images/32x32/view_sh.png" alt="" height="32" width="32" border="0"></td>
							<td align="left" width="120">Tourennummer:</td>
							<td align="left" width="160"><input type="text" name="tournummer" value="<?php echo $tournummer; ?>" size="17" class="Feld"></td>
							<td align="left" width="20"></td>
							<td align="left" width="100">Status:</td>
							<td align="left" width="160">
								<select class="Feld" name="status" size="1" >
									<option value="0"  <?php if($status==0){echo 'selected';}; ?>>Keine Auswahl</option>
									<?php echo touren_status_auswahl($status); ?>
								</select>
							</td>
							<td align="left" width="20"></td>
							<td width="130" align="left">Anmerkung:</td>
							<td width="110" align="right"><input type="text" name="anmerkung" value="<?php echo $anmerkung; ?>" size="25" class="Feld"></td>
						</tr>
						<tr>
							<td align="left" width="120">Kundennummer:</td>
							<td align="left" width="160">
								<table width="100%" border="0" cellspacing="0" cellpadding="0">
									<tr>
										<td>
											<?php echo tri_onkeyup_funktion('touren','kunden','','suchformular','kundennummer'); ?>
											<input onkeyup="startsearch_kunden_kundennummer(this.value)" type="text" name="kundennummer" value="<?php echo $kundennummer; ?>" size="17" class="Feld">
										</td>
										<td width="40" align="center"><?php echo tri_suchenlink('touren','kunden','','suchformular','kundennummer'); ?></td>
									</tr>
								</table>
							</td>
							<td align="left" width="20"></td>
							<td align="left" width="100"></td>
							<td align="left" width="160"></td>
							<td align="left" width="20"></td>
							<td align="left" width="130">Titel:</td>
							<td align="right" width="110"><input type="text" name="titel" value="<?php echo $titel; ?>" size="25" class="Feld"></td>
						</tr>
						<tr>
							<td align="center" width="120"></td>
							<td width="160"><a href="datachange.php?sucheloeschen=1"><img src="../images/16x16/delete2_sh.png" alt="" height="16" width="16" align="absmiddle" border="0">&nbsp;Suche zur&uuml;cksetzen</a></td>
							<td width="20"></td>
							<td width="100"></td>
							<td width="160"></td>
							<td width="20"></td>
							<td width="130"></td>
							<td align="right" width="110">
								<input type="hidden" name="sucheaktiv" value="1">
								<input type="hidden" name="seitenumschaltung" value="1">
								<input type="submit" name="submit" value="Suchen" class="Buttonsuchen">
							</td>
						</tr>
					</table>
				</div>
			</div>
		</form>
<?php 

		if($hinweisausgabe<>'')
		{ 
				echo hinweisausgabe('Fehler',$hinweisausgabe);
		}
?><br>
			<form id="FormName2" action="datachange.php" method="post" name="FormName2">
				<table width="95%" border="0" cellspacing="0" cellpadding="2" align="center">
					<tr>
						<td>
							<table width="100%" border="0" cellspacing="0" cellpadding="0">
								<tr>
									<td align="left"></td>
									<td align="right"><a href="eintragen.php"><img src="../images/24x24/add_sh.png" alt="" height="20" width="20" border="0"></a></td>
								</tr>
								<tr height="2">
									<td align="left" height="2"></td>
									<td align="right" height="2"></td>
								</tr>
							</table>
							<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#a1a1a1" class="tb_hover">
								<tr class="nohover">
										<td width="21" class="datachange_row_start">&nbsp;</td>
										<td width="70" class="datachange_row">Tour:</td>
										<td width="200" class="datachange_row">Tourentitel:</td>
										<td width="200" class="datachange_row">Gesamtstatus:</td>
										<td class="datachange_row">Anmerkung:</td>
										<td width="110" class="datachange_row">Anzahl Kunden:</td>
										<td width="100" class="datachange_row">Tourstart:</td>
										<td width="100" class="datachange_row">Tourende:</td>
										<td width="70" class="datachange_row_end">&nbsp;</td>										
								</tr>
								<?php
									$color1	="white";
									$color2	="#ececec";
									$color	= 1;	
									$wert1	= "touren.*";
									$wert2	= "count(touren.ID)";
									
									$sql_tour=	"	SELECT 		touren.*,
																COUNT(touren_kunden_zuordnung.kundennummer) as anzahl
													FROM 		touren
													LEFT JOIN 	touren_kunden_zuordnung ON touren_kunden_zuordnung.tour = touren.ID
												";
												
									if($tournummer>0)
									{
										$sql_tour = $sql_tour." WHERE touren.ID = '$tournummer' ";
									}
									
									if($kundennummer!='')
									{	
										if(strstr($sql_tour, 'WHERE')){
											$sql_tour = $sql_tour." AND touren_kunden_zuordnung.kundennummer = '$kundennummer' ";
										}else{
											$sql_tour = $sql_tour." WHERE touren_kunden_zuordnung.kundennummer = '$kundennummer' ";
										}
									}									
									if($anmerkung!='')
									{
										if(strstr($sql_tour, 'WHERE')){
											$anmerkung = '%'.$anmerkung.'%';
											$sql_tour = $sql_tour." AND touren.anmerkung_intern LIKE '$anmerkung' ";
										}else{
											$anmerkung = '%'.$anmerkung.'%';
											$sql_tour = $sql_tour." WHERE touren.anmerkung_intern LIKE '$anmerkung' ";
										}
									}
									if($titel!='')
									{
										if(strstr($sql_tour, 'WHERE')){
											$titel = '%'.$titel.'%';
											$sql_tour = $sql_tour." AND touren.titel LIKE '$titel' ";
										}else{
											$titel = '%'.$titel.'%';
											$sql_tour = $sql_tour." WHERE touren.titel LIKE '$titel' ";
										}
									}
									if($status>0)
									{
										if(strstr($sql_tour, 'WHERE'))
										{
											$sql_tour = $sql_tour." AND touren.status='$status' ";
										}
										else
										{
											$sql_tour = $sql_tour." WHERE touren.status='$status' ";
										}
									}
									
									$sql_tour.="	GROUP BY touren.ID
													ORDER BY touren.ID DESC ";
									
									$res 		= tri_db_query ("$datenbanknamecms", $sql_tour);
									while ($row = mysql_fetch_array ($res))
									{
										$result++;
										
										echo '
										<tr bgcolor="'.${"color".$color}.'" id="zeile'.$row[ID].'" ondblclick="javascript:document.location=\'zeigedaten.php?ID='.$row[ID].'\'">
											<td valign="top"><input type="checkbox" name="objekt'.$row[ID].'" value="1"></td>
											<td valign="top">'.$row['ID'].'</td>
											<td valign="top">'.$row['titel'].'</td>
											<td valign="top">';
											
											switch($row['status'])
											{
												case 3:	$color='green'; break;
												case 1:	$color='red'; break;
												case 9:	$color='red'; break;
												default: $color='orange';  break;
											}
											echo "<font color=\"$color\">Status: ".$status_array[$row['status']]."</font><br>";
											
											$res2 = tri_db_query ($datenbanknamecms, "SELECT tour FROM touren_kunden_zuordnung where tour='$row[ID]' limit 1");
											if(mysql_num_rows($res2)>0)
											{ 
												if($row['generiert']==1 or $row['generiert']==2)
												{ 
													echo "<font color=\"orange\">Tourenplan generiert</font>";
												}
												else
												{
													echo "<font color=\"red\">Tourenplan nicht generiert</font>";
												};
											}
											else
											{
												echo "<font color=\"red\">Keine Kunden</font>";
											};
											
											echo '</td>
											<td valign="top" align=left>'.$row['anmerkung_intern'].'</td>
											<td valign="top" align=right>'.$row['anzahl'].'</td>
											<td valign="top" align=right>'.datumwandeln_deutsch($row['tourstart'],1).'</td>
											<td valign="top" align=right>'.datumwandeln_deutsch($row['tourende'],1).'</td>
											<td valign="top" align="right"><a href="zeigedaten.php?ID='.$row[ID].'">bearbeiten</a></td>
										</tr>';
										if($color==1){$color=2;}else{$color=1;};
									};
								?>
							</table>
							<?php
								if($result=="0"){
									echo "<center><br><b>In dieser Auswahl sind keine Inhalte vorhanden</b></center> ";
								};
							?>
							<table width="100%" border="0" cellspacing="0" cellpadding="0">
								<tr>
									<td width="50%"><img src="../images/pfeil.jpg" alt="" height="30" width="30" border="0">
										<select name="aktion" size="1" class=feld >
											<option value="0">Aktion w&auml;hlen</option>
											<option value="1">Tour l&ouml;schen</option>									
										</select>
										<input type="submit" name="submit" value="Ausf&uuml;hren" class=Buttonausfuehren >
										<input type="hidden" name="aktionvorhanden" value="1">
										<input type="hidden" name="tournummer" value="<?php echo $row['ID']; ?>">
									</td>
									<td align="right"><a class="select_all">Alle w&auml;hlen</a> / <a class="deselect_all">entw&auml;hlen</a>
									</td>
									<td width="35" align="right"><a href="eintragen.php"><img src="../images/24x24/add_sh.png" alt="" height="20" width="20" border="0"></a></td>
								</tr>
								<tr>
									<td colspan="3" align="center">
										<?php echo seitenumschaltung($sql_tour,'datachange.php',1,$wert1,$wert2,false,'touren'); ?>
									</td>
								</tr>
							</table>							
						</td>
					</tr>
				</table>
			</form>
		
 		<script type="text/javascript" src="wz_tooltip.js"></script> 
	</body>
</html>
