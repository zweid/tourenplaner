<?php
	
	function touren_generieren($ID,$content_template=null,$pfadzusatz='',$template_typ='templatemitlogo',$headfoot_template=null)
	{
		if($ID<>null)
		{
			$res = tri_db_query ($GLOBALS['datenbanknamecms'], "SELECT * FROM touren where ID='$ID'");
			$row = mysql_fetch_array ($res);
			
			$GLOBALS['tri_conf']['cache']['touren']['row'][$ID] = $row;
			
			$pdf_vererbung		= (int) trieinstellungauslesen("administration","system","pdf_vererbung");
			$pfad 				= pathinfo(__FILE__);
			$pfadzusatz 		= (trim($pfadzusatz)=="") ? $pfad['dirname'].'/' : $pfadzusatz;
			
			$content_template 	= ($content_template==null) 	? kunden_kategorie_template('touren',$row['kundennummer'],$template_typ,$row['mandanten_ID']) : $content_template;
			$content_template 	= ($content_template==null || !file_exists($pfadzusatz.'tourentemplate/'.$content_template.'/class_v2.php')) ? trieinstellungauslesen("touren","system",$template_typ.$row['mandanten_ID']) : $content_template;
			$headfoot_template 	= ($headfoot_template=='templatemitlogo' || $headfoot_template==null) ? kunden_kategorie_template('standard',$row['kundennummer'],"templatemitlogo",$row['mandanten_ID']) : $headfoot_template;
			$headfoot_template 	= ($headfoot_template==null) 	? 'templatemitlogo_v2' : $headfoot_template;
			
			if(file_exists($pfadzusatz.'tourentemplate/'.$content_template.'/class_v2.php'))
			{
				if(file_exists('../standard/class.tri_pdf.php')==TRUE)
				{
					require_once('../standard/class.tri_pdf.php');
					$zieldatei='../touren/touren/'.$ID.'.pdf';
				}
				else
				{
					require_once('cmssystem/standard/class.tri_pdf.php');
					$zieldatei='cmssystem/touren/touren/'.$ID.'.pdf';
				}
				
				$class					= new tri_pdfoutput();
				$class->pdf_modul		= 'touren';
				$class->ID				= $ID;
				$class->template		= $content_template;
				$class->standardemplate	= $headfoot_template;
				$class->check_modul();	//Pruefe ob das Modul Class 2 unterstuetzt
				$class->init();			//Lade die PDF Klasse des neuen Moduls
				$class->typ				= 'content';
				$html_content			= $class->process();
				$class->typ				= 'header';
				$html_header			= $class->process();
				$class->typ				= 'footer';
				$html_footer			= $class->process();
				$pdfgen					= new tri_html_pdf();
				$pdfgen->content		= $html_content;
				$pdfgen->header			= $html_header;
				//echo $html_header;
				$pdfgen->footer			= $html_footer;
				$pdfgen->file			= $zieldatei;
				$pdfgen->config			= $class->config;
				$pdfgen->landscape		= $class->landscape;
				$pdfgen->margin			= $class->margin;
				$pdfgen->process();
			}else{
				die('Achtung, Sie ben�tigen die neuen PDFs der verbesserten PDF Klasse. Das Template '.$content_template.' wird nicht unterst&uuml;tzt !');
			};
		}
	};
	
	function touren_generieren_parser($ID,$ausgabe_array,$pfadzusatz="",$lang="")
	{
		$ausgabe 			= $ausgabe_array['content'];
		$kunden_temp 		= $ausgabe_array['kunden'];
		$tmpl_zusatzseiten	= $ausgabe_array['zusatzseiten'];

		if (!is_array($ID))
		{
			if(is_array($GLOBALS['tri_conf']['cache']['touren']['row'][$ID]))
			{
				$row_tour 	= $GLOBALS['tri_conf']['cache']['touren']['row'][$ID];
			}
			else
			{
				$res_tour = tri_db_query ($GLOBALS['datenbanknamecms'], "SELECT * FROM touren where ID='$ID'");
				$row_tour = mysql_fetch_array ($res_tour);
				$GLOBALS['tri_conf']['cache']['touren']['row'][$ID] = $row_tour;				
			}
		} 
		else 
		{
			$row_tour 	= $ID;
		}
		$zusatzinfos 	= $row_tour;
		
		//Kundenausgabe ON
				
		$kunden_tour_array 		= touren_kunden_array($ID);
		$count = 0;
		
		if(is_array($kunden_tour_array) && count($kunden_tour_array)>0)
		{
			foreach($kunden_tour_array as $key => $value)
			{
				$kundennummer 	= $value['kundennummer'];
				$pos 			= (($count)+1);
				$cache			= str_replace('{pos}',$pos,$kunden_temp);
				$cache			= kunden_textparsen_html_pdf($kunden_temp,$kundennummer,$ansprechpartner=false);
				$cache			= str_replace('{kunde_touren_anmerkung}',$value['anmerkung'],$cache);
				$kunden			.=	$cache;
				$count++;
			}
		}
		$ausgabe	= str_replace('{kunden}',$kunden,$ausgabe);
	
		//Kundenausgabe OFF
		
		//Anmerkungen ON
		
		$anmerkungsKeyArray = array('anmerkung_extern','anmerkung_extern2','anmerkung_extern3');

		foreach($anmerkungsKeyArray as $key => $value)
		{
			$anmerkung = $row_tour[$value];
		
			if (trim($row_tour[$value])=="")
			{
				$ausgabe 	= eregi_replace("\{if_".$value."\}([^\[]+)\{endif_".$value."\}","",$ausgabe);			
			} 
			else 
			{
				$ausgabe 	= eregi_replace("\{if_".$value."\}([^\[]+)\{endif_".$value."\}","\\1",$ausgabe);			
			}
			$ausgabe		= str_replace('{'.$value.'}',$anmerkung,$ausgabe);
		}

		//Anmerkungen OFF
		
		//Grunddaten ON
				
		$timestamp 	= datumnachtimestamp($row_tour['tourdatum']);
		$jahr		= date("Y", $timestamp);
		$monat		= date("m", $timestamp);
		$ausgabe	= str_replace('{datum_deutsch}',datumwandeln_deutsch($row_tour['datum']),$ausgabe);
		$ausgabe	= str_replace('{jahr}',$jahr,$ausgabe);
		$ausgabe	= str_replace('{monat}',$monat,$ausgabe);
		$ausgabe	= str_replace('{nummer}',tri_wawi_nummernausgabe('touren',$ID),$ausgabe);
		
		//Grunddaten OFF
	
		//Adressausgabe ON
		
		if(1==2 && 7==8)
		{
			//wird vorerst nicht ben�tigt, nur f�r den fall mal hier gelassen ;-)
			
			require_once(tri_modul_pfad('touren','kunden',__FILE__).'/funktionen.php');
	
			if(function_exists('kunden_separate_adresse_parsen'))
			{
				$ausgabe= kunden_separate_adresse_parsen($ausgabe,'touren',$row_tour,'adresse');
			}
		}
		
		//Adressausgabe OFF
	
		//VK-Ausgaben ON	
		
		if(1==2 && 7==8)
		{
			//wird vorerst nicht ben�tigt, nur f�r den fall mal hier gelassen ;-)
		
			if (file_exists(tri_modul_pfad('touren','standard',__FILE__).'/class.tri_auftragsverknuepfung.php'))
			{
				require_once(tri_modul_pfad('touren','standard',__FILE__).'/class.tri_auftragsverknuepfung.php');
				$belege_vk 	= new tri_auftragsverknuepfung($ID, 'touren');
				$belege_vk 	= $belege_vk->getIdStructure();
			}
			if(is_array($belege_vk) && count($belege_vk)>0)
			{
				/*
					parsed das template f�r alle anderen module zb yatego, magento etc
				*/
				foreach($belege_vk as $modulname => $daten)
				{
					if(is_int(strpos($ausgabe,'{'.$modulname.'_')))
					{
						require_once(tri_modul_pfad('touren',$modulname,__FILE__).'/funktionen.php');
						
						$functionname = $modulname.'_touren_parsen';
						
						if(function_exists($functionname))
						{
							$ausgabe = $functionname($ausgabe,$row_tour,$belege_vk);
						}
						else
						{
							//$ausgabe = preg_replace('/\{'.$modulname.'_([a-zA-Z0-9-_]+)\}/', '', $ausgabe);
						}
					}
				}
			}	
		}		
		$ausgabe = str_replace('{tour}', touren_tourdaten_fuer_pdf($row_tour['ID']), $ausgabe);
				
		return $ausgabe;
	}
	
	function touren_status($status=false)
	{
		$array = array(1 => 'angelegt', 2 => 'in Planung', 4 => 'auf Tour / Unterwegs', 3 => 'erledigt', 9  => 'storniert');
		return ($status==false) ? $array : $array[$status];
	}
	function touren_status_auswahl($status)
	{
		$array 		= touren_status();
		$optionen	= "";
		if(is_array($array) && count($array)>0)
		{
			foreach($array as $key => $value)
			{
				$select 	= ($key==$status) ? 'selected="selected"' : '';
				$optionen 	= $optionen.'<option value="'.$key.'" '.$select.'>'.$value.'</option>';
			}
		}
		return $optionen;
	}
	function touren_status_pruefen($status)
	{
		$success = array(3,9);
		
		if (in_array($status, $success))
		{
			return true;
		} 
		else 
		{
			return false;
		}
	};
	function touren_status_counter($status)
	{
		$res = tri_db_query ($GLOBALS['datenbanknamecms'], "SELECT count(ID) AS counter FROM touren WHERE status='$status'");
		$row = mysql_fetch_array ($res);
		return $row['counter'];
	}



	function touren_kunden_eintragen($kundenid, $ID)
	{
		$sql_needle = "SELECT * FROM touren_kunden_zuordnung WHERE tour='$ID';";
		$res_needle = tri_db_query($datenbanknamecms, $sql_needle);
		$arr_kundennummer = array();
		
		while($row_needle = mysql_fetch_array($res_needle)){
		
			foreach($row_needle as $key => $value){
				array_push($arr_kundennummer, $row_needle['kundennummer']);
			}
		}
		
		if(in_array($kundenid, $arr_kundennummer)){
			$hinweis = true;
		}else{
			$hinweis = false;
			tri_db_query ($datenbanknamecms, 
				"INSERT INTO 
					touren_kunden_zuordnung
				SET 
					tour					= '$ID',
					kundennummer 			= '$kundenid', 
					prio 					= '$prio';"
			);
		}
		return $hinweis;
	}
	
	function touren_kunden_eintragen_karte($kundenid, $ID){
		$sql_needle = "SELECT * FROM touren_kunden_zuordnung WHERE tour='$ID';";
		$res_needle = tri_db_query($datenbanknamecms, $sql_needle);
		$arr_kundennummer = array();
		while($row_needle = mysql_fetch_array($res_needle)){
			foreach($row_needle as $key => $value){
				array_push($arr_kundennummer, $row_needle['kundennummer']);
			}
		}
		
		if(in_array($kundenid, $arr_kundennummer)){
			$hinweis = true;
		}else{
			$hinweis = false;
			tri_db_query ($datenbanknamecms, 
				"INSERT INTO 
					touren_kunden_zuordnung
				SET 
					tour					= '$ID',
					kundennummer 			= '$kundenid';"
			);
		}
		return $hinweis;
	}

	function touren_array($ID=0){
		$array=array();
		if($ID>0){
			$sql="SELECT * FROM touren WHERE touren.ID='$ID'";
		}else{
			$sql="SELECT * FROM touren";
		}
		$res = mysql_db_query ($GLOBALS['datenbanknamecms'], $sql) or error_mysql_debugger(mysql_error(),__FILE__,__LINE__);
		while ($row = mysql_fetch_array ($res)){
			$array[]=$row;
			$count++;
		}
		return $array;
	};
	
	function touren_kunden_array($ID=0){
		$sql="SELECT * FROM touren, touren_kunden_zuordnung";
		if($ID>0){
			$sql.=" WHERE ID='$ID' AND touren.ID=touren_kunden_zuordnung.tour";
		}else{
			$sql.="	WHERE touren.ID=touren_kunden_zuordnung.tour";
		}
		$res = mysql_db_query ($GLOBALS['datenbanknamecms'], $sql) or error_mysql_debugger(mysql_error(),__FILE__,__LINE__);
		while ($row = mysql_fetch_array ($res)){
			$array[]=$row;
			$count++;
		}
				
		return $array;		
	}
	
	function touren_kunden_loeschen($ID)
	{
		$sql = "SELECT kundennummer FROM touren_kunden_zuordnung WHERE tour='$ID'";
		$res = tri_db_query ($GLOBALS['datenbanknamecms'], $sql);
		while ($row = mysql_fetch_array ($res)){			
			if(${"objekt_".$row['kundennummer']}==1){
				tri_db_query ($datenbanknamecms, "delete from touren_kunden_zuordnung where kundennummer='$row[kundennummer]'");				
			}
		}
	};
	function touren_loeschen($ID)
	{
		$sql 		= "SELECT * FROM touren WHERE ID='$ID'";
		$res 		= tri_db_query ($GLOBALS['datenbanknamecms'], $sql);
		while($row 	= mysql_fetch_array ($res))
		{
			tri_db_query ($GLOBALS['datenbanknamecms'], "delete from touren_kunden_zuordnung where tour='$ID'");			
		}
		tri_db_query ($GLOBALS['datenbanknamecms'], "DELETE FROM touren where ID='$ID'");	
	};
	
	function touren_auswahl($ID='')
	{
		$res = tri_db_query ($GLOBALS['datenbanknamecms'], "SELECT * FROM touren order by titel asc");
		while ($row = mysql_fetch_array ($res)){
			$ausgabe.="<option  value=\"".$row['ID'] ."\""; 
		
				if($ID==$row['ID']){
					$ausgabe.="selected";
				};
		
			$ausgabe.=">" .$row['titel'] . "</option>";
		};
		return $ausgabe;
	}
	
	function kunden_quickinsertbox()
	{
		$ausgabe = '
		<div class="slideBox" direction="left">
			<div class="slideBoxWrapper">
				<div class="vertikal_left vertikal"><span>K</span><span>u</span><span>n</span><span>d</span><span>e</span><span>n</span></div>
					<p class="slideBox_close slideBox_close_left setInputFocus">x</p>
			 		<p class="slideBox_show slideBox_show_left">+</p>
			 		<form action="zeigedaten.php" method="post" name="produktform" id="produktform">
					<p>Neuen Kunden hinzuf&uuml;gen:</p>
					<div class="content">
						<table width="500" border="0" cellspacing="0" cellpadding="2" align="center">
							<tr>
								<td rowspan="3" valign="top" width="50" align="center"><br/>
									<a><img src="../images/24x24/add2_sh.png" alt="" height="24" width="24" border="0"></a>
								</td>
								<td align="left" width="100">Kundennummer:</td>
								<td align="left">
									<table border="0" cellspacing="0" cellpadding="0">
										<tr>
											<td>'.kunden_onkeyup_funktion('eintragenformular','kundennummer').'<input type="text" name="kundennummer" value="'.$row['kundennummer'].'" size="23" class="Feld" onkeyup="startsearch_kunden(this.value)"></td>
											<td width="40" align="center">'.kunden_suchenlink('eintragenformular','kundennummer').'</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td align="left" width="100">Prio</td>
								<td align="left">
									<input type="text" name="prio" size="3" value="'.$maxprio.'" class="Feld" maxlength="3" >
								</td>
							</tr>
							<tr>
								<td align="left" width="100"><br/></td>
								<td align="left"><br/><input type="submit" name="submit" value="Einf&uuml;gen" class="Buttoneintragen"></td>
								<td align="left"><input type="hidden" name="ID" value="'.$ID.'"></td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>';
		return $ausgabe;
	}
	function touren_urlencode($wert)
	{
		$wert = str_replace(' ', '%20', $wert);
		$wert = str_replace(chr(246), 'oe', $wert);
		$wert = str_replace(chr(214), 'Oe', $wert);
		$wert = str_replace(chr(228), 'ae', $wert);
		$wert = str_replace(chr(196), 'Ae', $wert);
		$wert = str_replace(chr(252), 'ue', $wert);
		$wert = str_replace(chr(220), 'Ue', $wert);
		$wert = str_replace(chr(223), 'ss', $wert);
			
		return $wert;
	}
	function touren_geo_google_koordinaten($strasse,$plz,$ort)
	{
		$pfad		= tri_modul_pfad('touren','touren',__FILE__);
		$pfad       .= '/geo_xml_cache/';
		$strasse	= touren_urlencode($strasse);
		$plz		= touren_urlencode($plz);
		$ort		= touren_urlencode($ort);
		$md5 		= md5($strasse.'||'.$plz.'||'.$ort);
		$file_name 	= $pfad.$md5.'.xml';
		
		if(file_exists($pfad)==false)
		{
			mkdir($pfad);
		}		
		if(file_exists($file_name))
		{
			$code		= file_get_contents($file_name);			
		}
		else
		{
			$params = array
			(
				'http' => array
				(
					'method' 	=> 'POST',
					'header'	=> "content-type: text/plain\r\n"	
				)
			);
			//$google_api	= trieinstellungauslesen("administration","system","google_api");
			$file		= "https://maps.googleapis.com/maps/api/geocode/xml?address=".$strasse."+".$plz."+".$ort."&sensor=false";
			$code		= file_get_contents($file);//,false,stream_context_create($params);
			$speichern 	= file_put_contents($file_name, $code);			
		}
		$code		= trim($code);
		$XML 		= @simplexml_load_string($code);
		
		if(is_object($XML->result->geometry->location))
		{
			$geokoords		= (array) $XML->result->geometry->location->children();		
		}	
		else
		{
			//echo($strasse.' '.$plz.' '.$ort);			
		}	
		$lat 		= $geokoords['lat'];
		$lng 		= $geokoords['lng'];
		$koords 	= $lat.','.$lng;
			
		return $koords;
	}
	
	function touren_tourdaten_fuer_pdf($tournummer)
	{
		$tour_daten = array();

		$res_touren 		= tri_db_query($datenbanknamecms,"SELECT * FROM touren_kunden_zuordnung WHERE tour='$tournummer'");
		while($row_touren 	= mysql_fetch_assoc($res_touren))
		{
			$stamp_start 	= datumnachtimestamp($row_touren['datum_termin_start']);
			$stamp_end		= datumnachtimestamp($row_touren['datum_termin_ende']);
			$kw 			= date('W',$stamp_start);
			$tag 			= date('w',$stamp_start);
		
			for($i=1;$i<=5;$i++)
			{
				if(!is_array($tour_daten[$kw][$i]))
				{
					$tour_daten[$kw][$i] = array();
				}
			}
			for($i=$stamp_start;$i<=$stamp_end;$i=$i+86400)
			{
				$kw 		= date('W',$i);
				$tag 		= date('w',$i);
				$tour_daten[$kw][$tag][$row_touren['kundennummer']] = $row_touren;				
			}			
		}
	
		$days 			= array (1 => 'Montag', 2 => 'Dienstag', 3 => 'Mittwoch', 4 => 'Donnerstag', 5 => 'Freitag');
			
		$res_tour 		= tri_db_query($datenbanknamecms,"SELECT tourstart, tourende FROM touren WHERE ID=12");
		$row_tour 		= mysql_fetch_assoc($res_tour);
			
		$date_array 	= tri_errechne_wochen($row_tour['tourstart'],$row_tour['tourende']);
		$anzahl_wochen	= $date_array['gesamtanzahl'];
		$stamp 			= datumnachtimestamp($row_tour['tourstart']);
		$year 			= date("Y",$stamp);
		$mon			= date("m",$stamp);
		$day			= date('d',$stamp);
		$schritt 		= 0;
	
		$echo = '';
		$echo .= '<table border="1" cellspacing="0" cellpadding="0">';
		foreach($tour_daten as $kawe => $value_tage)
		{
			$echo .= '<tr>';
			foreach($value_tage as $tag => $touren)
			{
				$echo .= '<td valign="top">
				<table width="200px" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td colspan="5"><font size="1.0em"><strong>'.tage_array($tag).'</strong></font></td>
					</tr>
					<tr>
						<td width="10%" align="left"><font size="1.0em">KdNr.</font></td>
						<td width="30%" align="left"><font size="1.0em">Firmenname</font></td>
						<td width="10%" align="left"><font size="1.0em">PLZ</font></td>
						<td width="30%" align="left"><font size="1.0em">Adresse</font></td>
						<td width="20%" align="left"><font size="1.0em">Stadt</font></td>
					</tr>
					<tr>
						<td colspan="5" height="1" style="border-bottom: 1px solid;"></td>
					</tr>
					';
				foreach($touren as $kundennummer => $daten)
				{
					$echo .= '<tr>
								<td colspan="5" height="1" style="border-bottom: 1px solid;"></td>
							</tr>
							<tr>
								<td align="left"><font size="0.8em">'.$kundennummer.'</font></td>
								<td align="left"><font size="0.8em">'.kunden_datenfelder($kundennummer,'firmenfeld').'</font></td>
								<td align="left"><font size="0.8em">'.kunden_datenfelder($kundennummer,'plzfeld').'</font></td>
								<td align="left"><font size="0.8em">'.kunden_datenfelder($kundennummer,'strassenfeld').'</font></td>
								<td align="left"><font size="0.8em">'.kunden_datenfelder($kundennummer,'ortsfeld').'</font></td>
							</tr>';
				}
				$echo .= '</table>			
			</td>';
			}
			$echo .=  '</tr><tr>
						<td colspan="5" height="10">&nbsp;</td>
					</tr>';
		}
		$echo .=  '</table>';
	
		return $echo;
	}

	function touren_zeigedaten_informationen($modul,$daten=false)
	{
		$ausgabe = '
			<div class="slideBox" direction="right" style="width:500px;">
				<div class="slideBoxWrapper" style="min-height: 290px;">
					<div class="vertikal_right vertikal"><span>I</span><span>n</span><span>f</span><span>o</span><span>r</span><span>m</span><span>a</span><span>t</span><span>i</span><span>o</span><span>n</span><span>e</span><span>n</span></div>
			        <blockquote>
			            <p class="slideBox_close slideBox_close_right">x</p>
						<p class="slideBox_show slideBox_show_right">+</p>
						<p><img align="left" src="icon.png" alt="" border="0" height="32" width="32"></p>
						<p><h2>Informationen</h2></p>
						<hr noshade="noshade" size="1" />
						<p><img src="../images/32x32/gears_view_sh.png" align="left" height="32"></p>
						</p><h2>Anmerkung:</h2></p>
						<div style="overflow:auto;max-height:200px;">'.$daten['anmerkung_intern'].'</div>
					</blockquote>
				</div>
			</div>';
		return $ausgabe;
	};
	
?>